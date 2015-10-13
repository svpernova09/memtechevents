@servers(['prod' => 'memtechevents@halosrealm.com'])

@task('deploy:prod', ['on' => 'prod'])
cd /home/memtechevents/memtechevents.com
php artisan down
git pull origin master
composer install
php artisan migrate --force
php artisan up
@endtask
