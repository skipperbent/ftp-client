# pecee/ftp-client

A flexible FTP and SSL-FTP client for PHP.
This lib provides helpers easy to use to manage the remote files.


## Install

` composer require pecee/ftp-client`

## Getting Started

Connect to a server FTP :

```php
$ftp = new \FtpClient\FtpClient();
$ftp->connect($host, $sslEnabled, $port);
$ftp->login($login, $password);
```

Note: The connection is implicitly closed at the end of script execution (when the object is destroyed). Therefore it is unnecessary to call `$ftp->close()`, except for an explicit re-connection.


### Usage

Upload all files and all directories is easy:

```php
// upload with the BINARY mode
$ftp->uploadDirectory($sourceDirectory, $targetDirectory);

// Is equal to
$ftp->uploadDirectory($sourceDirectory, $targetDirectory, FTP_BINARY);

// or upload with the ASCII mode
$ftp->uploadDirectory($sourceDirectory, $targetDirectory, FTP_ASCII);
```

*Note : `FTP_ASCII` and `FTP_BINARY` are predefined PHP internal constants.*

Get a directory size:

```php
// size of the current directory
$size = $ftp->getDirectorySize();

// size of a given directory
$size = $ftp->getDirectorySize('/path/of/directory');
```

Count the items in a directory:

```php
// count in the current directory
$total = $ftp->count();

// count in a given directory
$total = $ftp->count('/path/of/directory');

// count only the "files" in the current directory
$total_file = $ftp->count('.', 'file');

// count only the "files" in a given directory
$total_file = $ftp->count('/path/of/directory', 'file');

// count only the "directories" in a given directory
$total_dir = $ftp->count('/path/of/directory', 'directory');

// count only the "symbolic links" in a given directory
$total_link = $ftp->count('/path/of/directory', 'link');
```

Detailed list of all files and directories:

```php
// scan the current directory and returns the details of each item
$items = $ftp->scanDirectory();

// scan the current directory (recursive) and returns the details of each item
var_dump($ftp->scanDirectory('.', true));
```

Result:

	'directory#www' =>
	    array (size=10)
	      'permissions' => string 'drwx---r-x' (length=10)
	      'number'      => string '3' (length=1)
	      'owner'       => string '32385' (length=5)
	      'group'       => string 'users' (length=5)
	      'size'        => string '5' (length=1)
	      'month'       => string 'Nov' (length=3)
	      'day'         => string '24' (length=2)
	      'time'        => string '17:25' (length=5)
	      'name'        => string 'www' (length=3)
	      'type'        => string 'directory' (length=9)

	  'link#www/index.html' =>
	    array (size=11)
	      'permissions' => string 'lrwxrwxrwx' (length=10)
	      'number'      => string '1' (length=1)
	      'owner'       => string '0' (length=1)
	      'group'       => string 'users' (length=5)
	      'size'        => string '38' (length=2)
	      'month'       => string 'Nov' (length=3)
	      'day'         => string '16' (length=2)
	      'time'        => string '14:57' (length=5)
	      'name'        => string 'index.html' (length=10)
	      'type'        => string 'link' (length=4)
	      'target'      => string '/var/www/shared/index.html' (length=26)

	'file#www/README' =>
	    array (size=10)
	      'permissions' => string '-rw----r--' (length=10)
	      'number'      => string '1' (length=1)
	      'owner'       => string '32385' (length=5)
	      'group'       => string 'users' (length=5)
	      'size'        => string '0' (length=1)
	      'month'       => string 'Nov' (length=3)
	      'day'         => string '24' (length=2)
	      'time'        => string '17:25' (length=5)
	      'name'        => string 'README' (length=6)
	      'type'        => string 'file' (length=4)


All FTP PHP functions are supported and some improved :

```php
// Requests execution of a command on the FTP server
$ftp->exec($command);

// Turns passive mode on or off
$ftp->pasv(true);

// Set permissions on a file via FTP
$ftp->chmod('0777', 'file.php');

// Removes a directory
$ftp->deleteFile('path/of/directory/to/remove');

// Removes a directory (recursive)
$ftp->deleteDirectory('path/of/directory/to/remove', true);

// Creates a directory
$ftp->createDirectory('path/of/directory/to/create');

// Creates a directory (recursive),
// creates automaticaly the sub directory if not exist
$ftp->createDirectory('path/of/directory/to/create', true);

// and more ...
```

Get the help information of remote FTP server:

```php
var_dump($ftp->help());
```

Result :

	array (size=6)
	  0 => string '214-The following SITE commands are recognized' (length=46)
	  1 => string ' ALIAS' (length=6)
	  2 => string ' CHMOD' (length=6)
	  3 => string ' IDLE' (length=5)
	  4 => string ' UTIME' (length=6)
	  5 => string '214 Pure-FTPd - http://pureftpd.org/' (length=36)


_Note : The result depend of FTP server._


### Extendable

Create your custom `FtpClient`.

```php
// MyFtpClient.php

/**
 * My custom FTP Client
 * @inheritDoc
 */
class MyFtpClient extends \FtpClient\FtpClient {

  public function removeByTime($path, $timestamp) {
      // your code here
  }

  public function search($regex) {
      // your code here
  }
}
```

```php
// example.php
$ftp = new MyFtpClient();
$ftp->connect($host);
$ftp->login($login, $password);

// remove the old files
$ftp->removeByTime('/www/mysite.com/demo', time() - 86400));

// search PNG files
$ftp->search('/(.*)\.png$/i');
```


## Testing

Using PHPUnit tests


## License

MIT (c) 2016.

## Based on code by  Nicolab
https://github.com/Nicolab/php-ftp-client