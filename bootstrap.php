<?php
// Install this directory under Cockpit addons/cockpit-image-upload/.
$app = $this;
// Use the REST dispatcher so Cockpit authenticates API tokens before dispatch.
$app->on('restApi.config', function ($restApi) {
    $file = __DIR__ . '/api/upload.post.php';
    $restApi->addEndPoint('/image-upload/upload', [
        'POST' => function ($params, $app) use ($file) {
            return (function () use ($file) {
                return include $file;
            })->call($app);
        },
    ]);
});
