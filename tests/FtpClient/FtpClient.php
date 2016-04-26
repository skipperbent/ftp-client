<?php
namespace tests\units\FtpClient;

use Pecee\FtpClient\FtpException;

class FtpClient extends \PHPUnit_Framework_TestCase
{

    protected $ftpHost = 'ftp.test.dk';
    protected $ftpUsername = 'username';
    protected $ftpPassword = 'password';
    protected $testDirectory = 'upload/tmp';
    protected $dirThatExists = 'upload/tmp';
    protected $dirWithMultipleFiles = 'upload/tmp';

    public function testWrongLogin() {
        $this->setExpectedException(FtpException::class);

        $ftpClient = new \Pecee\FtpClient\FtpClient();
        $ftpClient->connect($this->ftpHost);
        $ftpClient->login('nonExistingUser', 'nonExistingPassword');
    }

    public function testLogin() {
        $this->getClient();
    }

    public function testNonExistingDir() {
        $this->setExpectedException(FtpException::class);
        $ftpClient = $this->getClient();
        $ftpClient->scanDir('nonExisting');
    }

    public function testListDir() {
        $ftpClient = $this->getClient();
        $dir = $ftpClient->scanDir();

        $this->assertGreaterThan(0, count($dir));
    }

    public function testUploadFile() {
        $ftpClient = $this->getClient();
        $result = $ftpClient->uploadFile(__DIR__ . DIRECTORY_SEPARATOR . 'file_to_upload.gif', $this->testDirectory . '/test.gif', FTP_ASCII);
        $this->assertTrue(($result !== false));
    }

    public function testDeleteFile() {
        $ftpClient = $this->getClient();
        $result = $ftpClient->deleteFile($this->testDirectory . '/test.gif');

        $this->assertTrue($result);
    }

    public function testCreateDir() {
        $ftpClient = $this->getClient();
        $result = $ftpClient->createDirectory($this->testDirectory . '/test_dir');
        $this->assertTrue($result);
    }

    public function testDirectoryAlreadyExists() {
        $this->setExpectedException(FtpException::class);
        $ftpClient = $this->getClient();
        $ftpClient->createDirectory($this->testDirectory . '/test_dir');
    }

    public function testDeleteDirectory() {
        $ftpClient = $this->getClient();
        $ftpClient->deleteDirectory($this->testDirectory . '/test_dir');
    }

    protected function getClient() {
        $ftpClient = new \Pecee\FtpClient\FtpClient();
        $ftpClient->connect($this->ftpHost);
        $ftpClient->login($this->ftpUsername, $this->ftpPassword);
        return $ftpClient;
    }

}
