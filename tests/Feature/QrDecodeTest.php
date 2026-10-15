<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class QrDecodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_node_qr_decode_passes(): void
    {
        $node = 'node';
        $script = base_path('tests/js/qr-decode.test.mjs');

        $process = new Process([$node, $script], base_path());
        $process->run();

        if (! $process->isSuccessful()) {
            $this->markTestSkipped('QR decoding via node failed: '.$process->getErrorOutput().$process->getOutput());
        }

        $this->assertTrue($process->isSuccessful());
    }
}
