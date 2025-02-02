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
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function clearstatcache;
use function file_put_contents;
use function fopen;
use function fstat;
use function uniqid;
use function unlink;

/**
 * Test for unlink() functionality.
 *
 * @group  unlink
 */
class UnlinkTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @group  issue_51
     */
    public function canRemoveNonWritableFileFromWritableDirectory()
    {
        $structure = array('test_directory' => array('test.file' => ''));
        $root = vfsStream::setup('root', null, $structure);
        $root->getChild('test_directory')->chmod(0777);
        $root->getChild('test_directory')->getChild('test.file')->chmod(0444);
        $this->assertTrue(@unlink(vfsStream::url('root/test_directory/test.file')));
    }

    /**
     * @test
     * @group  issue_51
     */
    public function canNotRemoveWritableFileFromNonWritableDirectory()
    {
        $structure = array('test_directory' => array('test.file' => ''));
        $root = vfsStream::setup('root', null, $structure);
        $root->getChild('test_directory')->chmod(0444);
        $root->getChild('test_directory')->getChild('test.file')->chmod(0777);
        $this->assertFalse(@unlink(vfsStream::url('root/test_directory/test.file')));
    }

    /**
     * @test
     * @since  1.4.0
     * @group  issue_68
     */
    public function unlinkNonExistingFileTriggersError()
    {
        vfsStream::setup();
        try {
            $this->assertFalse(unlink('vfs://root/foo.txt'));
        } catch (\PHPUnit_Framework_Error $fe) {
            $this->assertEquals('unlink(vfs://root/foo.txt): No such file or directory', $fe->getMessage());
        }
    }
}
