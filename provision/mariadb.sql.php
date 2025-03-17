create database if not exists appfree_app_prod;
create database if not exists appfree_app_staging;
create database if not exists appfree_app_local;

drop user if exists 'appfree'@'localhost' ;
create user if not exists 'appfree'@'localhost' identified by '<?=getenv('pw')?>' ;

GRANT ALL PRIVILEGES ON appfree_app_prod.* TO 'appfree'@'localhost';
GRANT ALL PRIVILEGES ON appfree_app_staging.* TO 'appfree'@'localhost';
GRANT ALL PRIVILEGES ON appfree_app_local.* TO 'appfree'@'localhost';

FLUSH PRIVILEGES;
