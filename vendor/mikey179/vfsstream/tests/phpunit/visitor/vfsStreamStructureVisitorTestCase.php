<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests\visitor;

use bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\visitor\vfsStreamStructureVisitor.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class vfsStreamStructureVisitorTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function visitFileCreatesStructureForFile()
    {
        $structureVisitor = new vfsStreamStructureVisitor();
        $this->assertEquals(array('foo.txt' => 'test'),
                            $structureVisitor->visitFile(vfsStream::newFile('foo.txt')
                                                                  ->withContent('test')
                                               )
                                             ->getStructure()
        );
    }

    /**
     * @test
     */
    public function visitFileCreatesStructureForBlock()
    {
        $structureVisitor = new vfsStreamStructureVisitor();
        $this->assertEquals(array('[foo]' => 'test'),
                            $structureVisitor->visitBlockDevice(vfsStream::newBlock('foo')
                                                                  ->withContent('test')
                                               )
                                             ->getStructure()
        );
    }

    /**
     * @test
     */
    public function visitDirectoryCreatesStructureForDirectory()
    {
        $structureVisitor = new vfsStreamStructureVisitor();
        $this->assertEquals(array('baz' => array()),
                            $structureVisitor->visitDirectory(vfsStream::newDirectory('baz'))
                                             ->getStructure()
        );
    }

    /**
     * @test
     */
    public function visitRecursiveDirectoryStructure()
    {
        $root         = vfsStream::setup('root',
                                         null,
                                         array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                               'baz.txt' => 'world'
                                                         ),
                                               'foo.txt' => ''
                                         )
                        );
        $structureVisitor = new vfsStreamStructureVisitor();
        $this->assertEquals(array('root' => array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                                  'baz.txt' => 'world'
                                                                               ),
                                                                  'foo.txt' => ''
                                            ),
                            ),
                            $structureVisitor->visitDirectory($root)
                                             ->getStructure()
        );
    }
}
