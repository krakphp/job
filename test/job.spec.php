<?php

Mockery::globalHelpers();

describe('Krak Job', function() {
    describe('ProcessManager', function() {
        require_once __DIR__ . '/process-manager.php';
    });
    describe('Kernel', function() {
        require_once __DIR__ . '/kernel.php';
    });
});
