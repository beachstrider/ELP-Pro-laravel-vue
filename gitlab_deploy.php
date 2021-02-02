<?php

namespace Deployer;

require 'recipe/laravel.php';
// Project name
set('application', 'ALP');

// Use timestamp for release name
set('release_name', function () {
    return date('YmdHis');
});

set('keep_releases', 10);

// Project repository
set('repository', '');
set('composer_options', 'install');

set('http_user', 'www-data');

set('allow_anonymous_stats', false);

// SET artifact file names
set('build_file_stg', 'artifact_stg.tar.gz');
set('build_file_prod', 'artifact_prod.tar.gz');
// SET artifact folder
set('build_dir',  __DIR__ . '/artifact');
// SET files to exclude in artifacts
set('build_excludes_file', __DIR__ . '/build.exclude');

// Create artifact build path if it doesn't exist
set('build_path', function () {
    if (!test('[ -d {{build_dir}} ]')) {
        run('mkdir {{build_dir}}');
    }
    return get('build_dir');
});


// Build artifact
task('build:package-stg', 'tar --exclude-from={{build_excludes_file}} -czf {{build_path}}/{{build_file_stg}} .');
task('build:package-prod', 'tar --exclude-from={{build_excludes_file}} -czf {{build_path}}/{{build_file_prod}} .');

// Upload artifact CI
task('build:upload-stg', function () {
    upload(get('build_dir') . '/' . get('build_file_stg'), '{{release_path}}');
});
task('build:upload-prod', function () {
    upload(get('build_dir') . '/' . get('build_file_prod'), '{{release_path}}');
});
// Extract artifact on CI server
task('build:extract-stg', '
	tar -xzf {{release_path}}/{{build_file_stg}} -C {{release_path}};
	rm -rf {{release_path}}/{{build_file_stg}}
');
task('build:extract-prod', '
	tar -xzf {{release_path}}/{{build_file_prod}} -C {{release_path}};
	rm -rf {{release_path}}/{{build_file_prod}}
');

// NPM
task('npm:install', function () {
    run('cd {{release_path}}/admin/ && npm install');
});
task('npm:env-stg', function () {
    run('cd {{release_path}}/admin/ && printf "VUE_APP_API_URL=https://alp.stg.utilis.mk\n\nVUE_APP_STORAGE_PREFIX=il_\n\nVUE_APP_ENV=production" > .env.local');
});
task('npm:env-prod', function () {
    run('cd {{release_path}}/admin/ && printf "VUE_APP_API_URL=https://alp.alcolm.com\nVUE_APP_STORAGE_PREFIX=il_\nVUE_APP_ENV=production" > .env.local');
});
task('npm:build-stg', function () {
    run('cd {{release_path}}/admin/ && npm run build');
});
task('npm:build-prod', function () {
    run('cd {{release_path}}/admin/ && npm run build -- --mode production --dest dist_prod');
});
task('npm:publish-stg', function () {
    run('ls -la {{release_path}}/admin/dist');
    run('ls -la {{release_path}}/admin/dist/css');
    run('rm {{release_path}}/public/css/* -r');
    run('rm {{release_path}}/public/js/* -r');
    run('rm {{release_path}}/public/resources/* -r');
    run('cp {{release_path}}/admin/dist/css/* {{release_path}}/public/css/ -r');
    run('cp {{release_path}}/admin/dist/js/* {{release_path}}/public/js/ -r');
    run('cp {{release_path}}/admin/dist/resources/* {{release_path}}/public/resources/ -r');
    run('cp {{release_path}}/admin/dist/index.html {{release_path}}/resources/views/welcome.blade.php');
});
task('npm:publish-prod', function () {
    run('rm {{release_path}}/public/css/* -r');
    run('rm {{release_path}}/public/js/* -r');
    run('rm {{release_path}}/public/resources/* -r');
    run('cp {{release_path}}/admin/dist_prod/css/* {{release_path}}/public/css/ -r');
    run('cp {{release_path}}/admin/dist_prod/js/* {{release_path}}/public/js/ -r');
    run('cp {{release_path}}/admin/dist_prod/resources/* {{release_path}}/public/resources/ -r');
    run('cp {{release_path}}/admin/dist_prod/index.html {{release_path}}/resources/views/welcome.blade.php');
});
//Artisan
//task('artisan:passport:install', function (){ run('cd {{release_path}} && php artisan passport:install');});
task ('artisan:route:cache-alt', function (){
    try {
        run('cd {{release_path}} && php artisan route:cache');
    } catch (\Deployer\Exception\RuntimeException $e) {
        writeln('artisan route:cache fails, but allowed to continue');
    }
});

task('build', [
    'deploy:vendors',
    'npm:install',
    'npm:env-stg',
    'npm:build-stg',
    'npm:publish-stg',
    'build:package-stg',
    'npm:env-prod',
    'npm:build-prod',
    'npm:publish-prod',
    'build:package-prod',
])->desc('Build Artifacts');

task('deploy-build-stg', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'build:upload-stg',
    'build:extract-stg',
    'deploy:shared',
    'deploy:writable',
    'artisan:optimize:clear',
    'artisan:migrate',
    'artisan:storage:link',
    'artisan:view:cache',
    'artisan:config:cache',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Hosts
localhost('local')
    ->set('deploy_path', __DIR__ )
    ->set('release_path', __DIR__ )
    ->set('current_path', __DIR__ )
    ->stage('prod')
    ->roles('master');

host('stg_master')
    ->hostname('192.168.1.161')
    ->port('22')
    ->user('deployer')
    ->set('deploy_path', '/var/www/stg/alp')
    ->stage('staging')
    ->roles('master');
