
## How to run

```shell
#Copy .env.example to .env

# Alias sail
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'

# Start sail

sail up

#Generate key 
sail artisan key:generate

# run migrate
sail artisan migrate

```

## Config Lms
![Screen Shot 2023-10-27 at 10 56 03](https://github.com/luongtu996/lti-provider/assets/26574116/7b0e4cb4-b734-4ca8-95a8-71d508e70f5a)

![Screen Shot 2023-10-27 at 10 56 16](https://github.com/luongtu996/lti-provider/assets/26574116/74b9df76-0c68-459d-b51f-d6f8e634acf7)

![Screen Shot 2023-10-27 at 11 00 14](https://github.com/luongtu996/lti-provider/assets/26574116/e290cfd4-930c-4e66-86c3-977de8fed504)

![Screen Shot 2023-10-27 at 11 01 49](https://github.com/luongtu996/lti-provider/assets/26574116/c3d6a859-30f3-4ac6-b420-8140acc78c95)

## Config Lti tool

Config in `Database\Seeders\LtiSeeder;`

<img width="836" alt="Screen Shot 2023-10-27 at 11 03 37" src="https://github.com/luongtu996/lti-provider/assets/26574116/11baf554-7dc8-427a-a250-60f904d4a72d">

Then run

```shell
sail artisan db:seed --class=LtiSeeder
```
## Reproduce
![Screen Shot 2023-10-27 at 11 08 41](https://github.com/luongtu996/lti-provider/assets/26574116/89c706bc-0c3d-4f06-bef8-2172bba20b7d)
<img width="1188" alt="Screen Shot 2023-10-27 at 11 11 18" src="https://github.com/luongtu996/lti-provider/assets/26574116/68ba63a8-925c-4230-a249-4d4cfed3cd97">

## FIX lỗi không submit grade.

là do lms không get được `{app}/keys`. 
Sửa jwk method =  PublicJWK.
Copy json từ  url `{app}/keys` paste vào

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
