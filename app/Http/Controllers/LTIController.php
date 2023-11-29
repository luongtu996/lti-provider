<?php

namespace App\Http\Controllers;

//use App\Models\LtiKey;
use App\Lti\Lti13Cache;
use App\Lti\Lti13Cookie;
use App\Lti\Lti13Database;
use App\Services\Lti\LtiMessageLaunch;
use App\Services\Lti13Service;
use Carbon\Carbon;
use DateTimeInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Packback\Lti1p3\LtiDeepLinkResource;
use Packback\Lti1p3\LtiGrade;
use Packback\Lti1p3\LtiLineitem;
use Packback\Lti1p3\LtiOidcLogin;

class LTIController extends Controller
{
    public $lti13Service;
    public $cookie;

    /**
     * Create a new controller instance.
     *
     * HomeController constructor.
     */
    public function __construct(
        Lti13Service $lti13Service,
        Lti13Cookie  $cookie
    )
    {
        $this->lti13Service = $lti13Service;
        $this->cookie = $cookie;
    }

    public function login(Request $request)
    {
        $url = $this->lti13Service->login($request);
        return redirect($url);
    }

    public function handleRedirectAfterLogin(Request $request)
    {
        try {
            $launch = $this->lti13Service->validateLaunch($request);
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'message' => 'Authentication failed'), 400);
        }
        Log::info($launch->getLaunchData());
        if ($launch->isDeepLinkLaunch()) {
            return view('lti.quiz-level-select', array('launch_id' => $launch->getLaunchId()));
        }
        $target_url = $launch->getLaunchData()['https://purl.imsglobal.org/spec/lti/claim/target_link_uri'];
        return response()->redirectTo($target_url . '?launch_id=' . $launch->getLaunchId());
    }

    public function greeting(Request $request)
    {
        return "Hello world";
    }

    public function selectQuizLevel()
    {
        $queries = request()->query->all();
        $launch_id = $queries['launch_id'];
        $level = $queries['level'];

        $launch = $this->lti13Service->getCachedLaunch($launch_id);
        if (!$launch->isDeepLinkLaunch()) {
            return response()->json(array('success' => false, 'message' => 'Must be a deep link launch!'), 400);
        }
        $resource = LtiDeepLinkResource::new()
            ->setUrl(env('APP_URL') . '/do-quiz')
            ->setCustomParams(array('level' => $level))
            ->setTitle('The ' . $level . ' quiz!');

        $dl = $launch->getDeepLink();

        $dl->outputResponseForm([$resource]);
    }

    public function doQuiz()
    {
        $launch_id = request()->query->get('launch_id');
        $launch = $this->lti13Service->getCachedLaunch($launch_id);
        try {
            $level = $launch->getLaunchData()['https://purl.imsglobal.org/spec/lti/claim/custom']['level'];
            return view('lti.do-quiz', array('level' => $level, 'launch_id' => $launch_id));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'message' => 'Required param [level] missing!'), 400);
        }
    }

    public function handleQuizSubmitted()
    {
        $req = request()->only(['launch_id', 'ans']);
        $launch_id = $req['launch_id'];
        $ans = $req['ans'];
        $launch = $this->lti13Service->getCachedLaunch($launch_id);
        if (!$launch->hasAgs()) {
            return response()->json(array('success' => false, 'message' => 'Do not have grades!'), 400);
        }
        try {
            $launch_data = $launch->getLaunchData();
            $level = $launch_data['https://purl.imsglobal.org/spec/lti/claim/custom']['level'];
            $correctAnswer = $this->getCorrectAnswer($level);
            $point = 0;
            if ($ans == $correctAnswer) {
                $point = 100;
            }

            $grades = $launch->getAgs(); //Assignments and grades services
            $score = LTIGrade::new()
                ->setScoreGiven($point)
                ->setScoreMaximum(100) //This one will be compared with the maximum set on LMS and respectively calculated
                ->setTimestamp(Carbon::now()->toIso8601String())
                ->setActivityProgress('Completed')
                ->setGradingProgress('FullyGraded')
                ->setUserId($launch_data['sub']);

            $grades->putGrade($score);
            $res = $correctAnswer == $ans ? 'correct' : 'incorrect';
            return response()->redirectTo(env('APP_URL') . '/quiz-completed?res=' . $res . '&launch_id=' . $launch_id);
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'message' => 'Can not return score!'), 400);
        }
    }

    public function quizCompleted()
    {
        $res = request()->query->get('res');
        $launch_id = request()->query->get('launch_id');
        $launch = $this->lti13Service->getCachedLaunch($launch_id);

        $launch_data = $launch->getLaunchData();
        $data = [];
        if ($launch->hasNrps() && $launch->hasAgs()) {

            $nrps = $launch->getNrps();
            $members = $nrps->getMembers();
            $ags = $launch->getAgs();
            $scores = $ags->getGrades();

            usort($scores, function ($a, $b) {
                return $b['resultScore'] - $a['resultScore'];
            });
            foreach ($scores as $score) {
                $userid = $score['userId'];
                $user = null;
                foreach ($members as $mem) {
                    if ($mem['user_id'] == $userid) {
                        $user = $mem;
                        break;
                    }
                }
                $data[] = array(
                    'name' => $user['name'],
                    'score' => $score['resultScore']
                );
            }
        }
        return view('lti.quiz-result', array('result' => $res, 'ranking' => $data));
    }

    /**
     * @throws Exception
     */
    private function getCorrectAnswer($level)
    {
        switch ($level) {
            case 'easy':
                return 2;
            case 'medium':
                return 20;
            case 'hard':
                return 40;
            default:
                throw new Exception('Invalid level');
        }
    }

    public function jwks()
    {
        return new JsonResponse($this->lti13Service->getPublicJwks());
    }

    public function config(Request $request)
    {
        $configArray = [
            "title" => "Assessment",
            "description" => "Assessment Response Tool",
            "oidc_initiation_url" => url("/login"),
            "target_link_uri" => url("/"),
            "scopes" => [
                "https://purl.imsglobal.org/spec/lti-ags/scope/score",
                "https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly",
                "https://purl.imsglobal.org/spec/lti-nrps/scope/contextmembership.readonly",
                "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly",
                "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem"
            ],
            "extensions" => [
                [
                    "domain" => $request->getHost(),
                    "tool_id" => "assessment",
                    "platform" => "canvas-dev.heyhi.sg",
                    "settings" => [
                        "privacy_level" => "public",
                        "text" => "Launch Assessment",
                        "placements" => [
                            [
                                "text" => "Assessment",
                                "enabled" => true,
                                "placement" => "assignment_selection",
                                "message_type" => "LtiDeepLinkingRequest",
                                "target_link_uri" => url("/"),
                            ],
                            [
                                "text" => "Assessment",
                                "enabled" => true,
                                "placement" => "link_selection",
                                "message_type" => "LtiDeepLinkingRequest",
                                "target_link_uri" => url("/"),
                            ]
                        ]
                    ]
                ]
            ],
            "public_jwk" => [
                "kty" => "RSA",
                "alg" => "RS256",
                "use" => "sig",
                "e" => "AQAB",
                "n" => "oiQQO8ilHqJS2ukVsPYMgVr2v0Xy1oH8zJwDfUDe46sKQVuMMIBIBEOFMI4gPvjbzrCCrIyacj3VUXUq21RW-hLYozfikIe_iVhFbrEmXM2qlqNg6kwMNjoMKm3pRHFjOWwdZy0mGwA48nl788_n9pO26whwwFgOI5I6flGHj-_b9up8pcnrXsLux_yu759-sGJufiItxX4TnI0vgGJF9fV7ZACnSTjIWGCg2Lq7VcA2JYoNbly0FOOIUHQrc87es1F2WzwfjkWNYXlPqSr7js8cRikNar3WAcqNfAiLP6tOSp2nknLhb2FOsdYAXvXM1zguMmByZV2x4PML9ItJ3w==",
                "kid" => "b3db2805-4de5-49b0-bc9e-4ed89c1b21db"
            ],

            "custom_fields" => [
                "canvas_integration_id" => '$Canvas.user.sisSourceId',
                "user_username" => '$User.username',
                "canvas_user_id" => '$Canvas.user.id',
                "canvas_course_id" => '$Canvas.course.id'
            ]
        ];

        return response()->json($configArray);
    }
}
