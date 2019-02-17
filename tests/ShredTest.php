<?php

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

final class ShredTest extends TestCase
{
  private $root;
  private $rootName = 'home';
  private $testFile = 'test';
  private $testFolder = 'testFolder';

  public function setUp()
  {
    vfsStreamWrapper::register();
    $this->root = vfsStream::setup($this->rootName);
    file_put_contents(vfsStream::url("{$this->rootName}/{$this->testFile}"), '1 2 3 4 5 6');
    mkdir(vfsStream::url("{$this->rootName}/{$this->testFolder}"));
  }

  public function testCanShred()
  {
    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $oldContent = file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}"));
    $shred = new Shred\Shred();

    $this->assertEquals(
      true,
      $shred->shred(vfsStream::url("{$this->rootName}/{$this->testFile}"), false)
    );

    $newContent = file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}"));

    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $this->assertNotEquals(
      $oldContent,
      $newContent
    );
  }

  public function testCanShredAndDelete()
  {
    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $shred = new Shred\Shred();

    $this->assertEquals(
      true,
      $shred->shred(vfsStream::url("{$this->rootName}/{$this->testFile}"), true)
    );

    $this->assertFileNotExists(
      vfsStream::url("{$this->rootName}/{$this->testFile}")
    );
  }

  public function testCanNotShredDirectory()
  {
    $shred = new Shred\Shred();

    $this->assertFalse(
      $shred->shred(vfsStream::url("{$this->rootName}/{$this->testFolder}"), true)
    );

    $this->assertFileExists(
      vfsStream::url("{$this->rootName}/{$this->testFolder}")
    );
  }

  public function testStats()
  {
    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $oldContent = file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}"));
    $shred = new Shred\Shred(3, 3, true);
    $this->setOutputCallback(function () {
      // noop
    });

    $this->assertEquals(
      true,
      $shred->shred(vfsStream::url("{$this->rootName}/{$this->testFile}"), false)
    );

    $newContent = file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}"));

    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $this->assertNotEquals(
      $oldContent,
      $newContent
    );

    $this->assertContains(
      "iterations: 3\n",
      $this->getActualOutput()
    );

    $this->assertContains(
      "block size: 3\n",
      $this->getActualOutput()
    );

    $this->assertContains(
      "took: ",
      $this->getActualOutput()
    );
  }

  public function testStatsCustom()
  {
    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $oldContent = file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}"));
    $shred = new Shred\Shred(5, 6, true);
    $this->setOutputCallback(function () {
      // noop
    });

    $this->assertEquals(
      true,
      $shred->shred(vfsStream::url("{$this->rootName}/{$this->testFile}"), false)
    );

    $newContent = file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}"));

    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $this->assertNotEquals(
      $oldContent,
      $newContent
    );

    $this->assertContains(
      "iterations: 5\n",
      $this->getActualOutput()
    );

    $this->assertContains(
      "block size: 6\n",
      $this->getActualOutput()
    );

    $this->assertContains(
      "took: ",
      $this->getActualOutput()
    );
  }

  public function testStatsDelete()
  {
    $this->assertEquals(
      11,
      strlen(file_get_contents(vfsStream::url("{$this->rootName}/{$this->testFile}")))
    );

    $shred = new Shred\Shred(3, 3, true);
    $this->setOutputCallback(function () {
      // noop
    });

    $this->assertEquals(
      true,
      $shred->shred(vfsStream::url("{$this->rootName}/{$this->testFile}"), true)
    );

    $this->assertContains(
      "iterations: 3\n",
      $this->getActualOutput()
    );

    $this->assertContains(
      "block size: 3\n",
      $this->getActualOutput()
    );

    $this->assertContains(
      "took: ",
      $this->getActualOutput()
    );

    $this->assertContains(
      "successfully deleted vfs://{$this->rootName}/{$this->testFile}",
      $this->getActualOutput()
    );
  }
}
