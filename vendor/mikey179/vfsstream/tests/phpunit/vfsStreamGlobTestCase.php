<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use const GLOB_MARK;
use function bovigo\assert\assertEmpty;
use function glob;
use function mkdir;

/**
 * Test for bovigo\vfs\vfsStream.
 *
 * @since       0.9.0
 * @group       issue_2
 */
class vfsStreamGlobTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function globDoesNotWorkWithVfsStreamUrls()
    {
        $root = vfsStream::setup('example');
        mkdir(vfsStream::url('example/test/'), 0777, true);
        $this->assertEmpty(glob(vfsStream::url('example'), GLOB_MARK));
    }
}
