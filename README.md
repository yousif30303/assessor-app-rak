<p align="center"><a href="https://www.bdc.ae" target="_blank"><img src="https://www.bdc.ae/wp-content/uploads/2022/06/logo_bdc.png" width="400" alt="Laravel Logo"></a></p>



## Assessor App Api

For deployment follow the below steps:

- Create new .env file and enter all config constants from example.env
- configure mysql, sqlsrv and oracle in .env file
- Run command: composer update
- Run command: php artisan key:generate.
- Run command: php artisan migrate
- Run command: php artisan db:seed --database=mysql/sqlsrv

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
