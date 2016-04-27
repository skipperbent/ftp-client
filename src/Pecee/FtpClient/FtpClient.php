<?php
/*
 * This file is part of the `nicolab/php-ftp-client` package.
 *
 * (c) Nicolas Tallefourtane <dev@nicolab.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Nicolas Tallefourtane http://nicolab.net
 */
namespace Pecee\FtpClient;

/**
 * The FTP and SSL-FTP client for PHP.
 *
 * @method bool alloc() alloc(int $fileSize, string &$result = null) Allocates space for a file to be uploaded
 * @method bool cdup() cdup() Changes to the parent directory
 * @method bool chdir() chdir(string $directory) Changes the current directory on a FTP server
 * @method int chmod() chmod(int $mode, string $filename) Set permissions on a file via FTP
 * @method bool close() close() Closes an FTP connection
 * @method bool delete() delete(string $remotePath) Deletes a file on the FTP server
 * @method bool exec() exec(string $command) Requests execution of a command on the FTP server
 * @method bool fget() fget(resource $handle, string $remoteFile, int $mode, int $resumePosition = 0) Downloads a file from the FTP server and saves to an open file
 * @method bool fput() fput(string $remoteFile, resource $handle, int $mode, int $startPosition = 0) Uploads from an open file to the FTP server
 * @method mixed get_option() get_option(int $option) Retrieves various runtime behaviours of the current FTP stream
 * @method bool get() get(string $localFile, string $remoteFile, int $mode, int $resumePosition = 0) Downloads a file from the FTP server
 * @method int mdtm() mdtm(string $remoteFile) Returns the last modified time of the given file
 * @method int nb_continue() nb_continue() Continues retrieving/sending a file (non-blocking)
 * @method int nb_fget() nb_fget(resource $handle, string $remoteFile, int $mode, int $resumePosition = 0) Retrieves a file from the FTP server and writes it to an open file (non-blocking)
 * @method int nb_fput() nb_fput(string $remoteFile, resource $handle, int $mode, int $startPosition = 0) Stores a file from an open file to the FTP server (non-blocking)
 * @method int nb_get() nb_get(string $localFile, string $remoteFile, int $mode, int $resumePosition = 0) Retrieves a file from the FTP server and writes it to a local file (non-blocking)
 * @method int nb_put() nb_put(string $remoteFile, string $localFile, int $mode, int $startPosition = 0) Stores a file on the FTP server (non-blocking)
 * @method bool pasv() pasv(bool $pasv) Turns passive mode on or off
 * @method bool put() put(string $remoteFile, string $localFile, int $mode, int $startPosition = 0) Uploads a file to the FTP server
 * @method string pwd() pwd() Returns the current directory name
 * @method bool quit() quit() Closes an FTP connection
 * @method array raw() raw(string $command) Sends an arbitrary command to an FTP server
 * @method bool rename() rename(string $oldName, string $newName) Renames a file or a directory on the FTP server
 * @method bool set_option() set_option(int $option, mixed $value) Set miscellaneous runtime FTP options
 * @method bool site() site(string $command) Sends a SITE command to the server
 * @method int size() size(string $remoteFile) Returns the size of the given file
 * @method string systype() systype() Returns the system type identifier of the remote FTP server
 * @method nlist(string $directory) Returns a list of files in the given directory
 */

class FtpClient implements \Countable
{
    /**
     * The connection with the server
     *
     * @var resource
     */
    protected $connection;

    /**
     * Constructor.
     * @param  resource|null $connection
     * @throws FtpException  If ftp extension is not loaded.
     */
    public function __construct($connection = null)
    {
        if (!extension_loaded('ftp')) {
            throw new FtpException('FTP extension is not loaded!');
        }

        if ($connection) {
            $this->connection = $connection;
        }
    }

    /**
     * Close the connection when the object is destroyed
     */
    public function __destruct()
    {
        if ($this->connection) {
            $this->close();
        }
    }

    /**
     * Overwrites the PHP limit
     *
     * @param string|null $memory The memory limit, if null is not modified
     * @param int $timeLimit The max execution time, unlimited by default
     * @param bool $ignoreUserAbort Ignore user abort, true by default
     * @return static
     */
    public function setPhpLimit($memory = null, $timeLimit = 0, $ignoreUserAbort = true)
    {
        if (null !== $memory) {
            ini_set('memory_limit', $memory);
        }

        ignore_user_abort($ignoreUserAbort);
        set_time_limit($timeLimit);

        return $this;
    }

    /**
     * Get the help information of the remote FTP server.
     * @return array
     */
    public function help()
    {
        return $this->raw('help');
    }

    /**
     * Open a FTP connection
     *
     * @param string $host
     * @param bool   $ssl
     * @param int    $port
     * @param int    $timeout
     *
     * @return static
     * @throws FtpException If unable to connect
     */
    public function connect($host, $ssl = false, $port = 21, $timeout = 90)
    {
        if ($ssl) {
            $this->connection = @ftp_ssl_connect($host, $port, $timeout);
        } else {
            $this->connection = @ftp_connect($host, $port, $timeout);
        }

        if (!$this->connection) {
            throw new FtpException('Unable to connect');
        }

        return $this;
    }

    /**
     * Get the connection with the server
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Logs in to an FTP connection
     *
     * @param string $username
     * @param string $password
     *
     * @throws FtpException
     * @return static
     */
    public function login($username = 'anonymous', $password = '')
    {
        $result = @ftp_login($this->connection, $username, $password);

        if ($result === false) {
            throw new FtpException('Login incorrect');
        }

        return $this;
    }

    /**
     * Returns the last modified time of the given file.
     * Return -1 on error
     *
     * @param string $remoteFile
     * @param string|null $format
     *
     * @return int
     */
    public function getModifiedTime($remoteFile, $format = null)
    {
        $time = $this->mdtm($remoteFile);

        if ($time !== -1 && $format !== null) {
            return date($format, $time);
        }

        return $time;
    }

    /**
     * Changes to the parent directory
     *
     * @throws FtpException
     * @return static
     */
    public function up()
    {
        $result = @$this->cdup();

        if ($result === false) {
            throw new FtpException('Unable to get parent folder');
        }

        return $this;
    }

    /**
     * Returns a list of files in the given directory
     *
     * @param string   $directory The directory, by default is "." the current directory
     * @param bool     $recursive
     * @param string $filter    A callable to filter the result, by default is asort() PHP function.
     *                            The result is passed in array argument,
     *                            must take the argument by reference !
     *                            The callable should proceed with the reference array
     *                            because is the behavior of several PHP sorting
     *                            functions (by reference ensure directly the compatibility
     *                            with all PHP sorting functions).
     *
     * @return array
     * @throws FtpException If unable to list the directory
     */
    public function getFilesList($directory = '.', $recursive = false, $filter = 'sort')
    {
        if (!$this->isDirectory($directory)) {
            throw new FtpException('"'.$directory.'" is not a directory');
        }

        $files = $this->nlist($directory);

        if ($files === false) {
            throw new FtpException('Unable to list directory');
        }

        $result  = array();
        $dir_len = strlen($directory);

        // if it's the current
        if (false !== ($kdot = array_search('.', $files))) {
            unset($files[$kdot]);
        }

        // if it's the parent
        if(false !== ($kdot = array_search('..', $files))) {
            unset($files[$kdot]);
        }

        if (!$recursive) {
            foreach ($files as $file) {
                $result[] = $directory.'/'.$file;
            }

            // working with the reference (behavior of several PHP sorting functions)
            $filter($result);

            return $result;
        }

        // utils for recursion
        $flatten = function (array $arr) use (&$flatten) {

            $flat = [];

            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $flat = array_merge($flat, $flatten($v));
                } else {
                    $flat[] = $v;
                }
            }

            return $flat;
        };

        foreach ($files as $file) {
            $file = $directory.'/'.$file;

            // if contains the root path (behavior of the recursivity)
            if (0 === strpos($file, $directory, $dir_len)) {
                $file = substr($file, $dir_len);
            }

            if ($this->isDirectory($file)) {
                $result[] = $file;
                $items    = $flatten($this->getFilesList($file, true, $filter));

                foreach ($items as $item) {
                    $result[] = $item;
                }

            } else {
                $result[] = $file;
            }
        }

        $result = array_unique($result);

        $filter($result);

        return $result;
    }

    /**
     * Creates a directory
     *
     * @param  string $directory The directory
     * @param  bool   $recursive
     * @return bool
     * @throws FtpException
     */
    public function createDirectory($directory, $recursive = false)
    {
        if($this->isDirectory($directory)) {
            throw new FtpException('Directory already exists');
        }

        if (!$recursive) {
            return (@$this->mkdir($directory) !== false);
        }

        $result = false;
        $pwd = $this->pwd();
        $parts = explode('/', $directory);

        foreach ($parts as $part) {

            if (!@$this->chdir($part)) {
                $result = @$this->mkdir($part);
                $this->chdir($part);
            }
        }

        $this->chdir($pwd);

        return ($result !== false);
    }

    /**
     * Remove a directory.
     * @param  string       $directory
     * @param  bool         $recursive Forces deletion if the directory is not empty
     * @return bool
     * @throws FtpException If unable to list the directory to remove
     */
    public function deleteDirectory($directory, $recursive = true)
    {
        if(!$this->isDirectory($directory)) {
            throw new FtpException('Directory does not exist');
        }

        if ($recursive) {
            $files = $this->getFilesList($directory, false, 'rsort');

            // remove children
            foreach ($files as $file) {
                $this->delete($file, true);
            }
        }

        // remove the directory
        return $this->rmdir($directory);
    }

    /**
     * Empty directory
     *
     * @param  string $directory
     * @return bool
     */
    public function cleanDirectory($directory)
    {
        if(!$files = $this->getFilesList($directory)) {
            return $this->isEmpty($directory);
        }

        // remove children
        foreach ($files as $file) {
            $this->deleteFile($file, true);
        }

        return $this->isEmpty($directory);
    }

    /**
     * Remove a file or a directory
     * @param  string $path      The path of the file or directory to remove
     * @return bool
     */
    public function deleteFile($path)
    {
        return @$this->delete($path);
    }

    /**
     * Check if a directory exist.
     * @param $directory
     * @throws FtpException
     * @return bool
     */
    public function isDirectory($directory)
    {
        $pwd = $this->pwd();

        if ($pwd === false) {
            throw new FtpException('Unable to resolve the current directory');
        }

        if (@$this->chdir($directory)) {
            $this->chdir($pwd);
            return true;
        }

        $this->chdir($pwd);

        return false;
    }

    /**
     * Check if a directory is empty
     * @param  string $directory
     * @return bool
     */
    public function isEmpty($directory)
    {
        return $this->count($directory, null, false) === 0 ? true : false;
    }

    /**
     * Scan a directory and returns the details of each item.
     * @param  string $directory
     * @param  bool   $recursive
     * @return array
     */
    public function scanDir($directory = '.', $recursive = false)
    {
        return $this->parseList($this->getList($directory, $recursive));
    }

    /**
     * Returns the total size of the given directory in bytes
     *
     * @param  string $directory The directory, by default is the current directory.
     * @param  bool   $recursive true by default
     * @return int    The size in bytes.
     */
    public function getDirectorySize($directory = '.', $recursive = true)
    {
        $items = $this->scanDir($directory, $recursive);
        $size  = 0;

        foreach ($items as $item) {
            $size += (int) $item['size'];
        }

        return $size;
    }

    /**
     * Count the items (file, directory, link, unknown)
     * @param  string      $directory The directory, by default is the current directory.
     * @param  string|null $type      The type of item to count (file, directory, link, unknown)
     * @param  bool        $recursive true by default
     * @return int
     */
    public function count($directory = '.', $type = null, $recursive = true)
    {
        $items  = (null === $type ? $this->getFilesList($directory, $recursive)
            : $this->scanDir($directory, $recursive));

        $count = 0;
        foreach ($items as $item) {
            if (null === $type or $item['type'] == $type) {
                $count++;
            }
        }

        return $count;
    }

    public function uploadFile($localFile, $remotePath) {
        return $this->put($remotePath, $localFile, FTP_ASCII);
    }

    /**
     * Uploads a file to the server from a string
     *
     * @param  string       $remote_file
     * @param  string       $content
     * @return static
     * @throws FtpException When the transfer fails
     */
    public function uploadFromString($remote_file, $content)
    {
        $handle = fopen('php://temp', 'w');

        fwrite($handle, $content);
        rewind($handle);

        if ($this->fput($remote_file, $handle, FTP_BINARY)) {
            return $this;
        }

        throw new FtpException('Unable to put the file "'.$remote_file.'"');
    }

    /**
     * Upload files
     * @param  string $sourceDirectory
     * @param  string $targetDirectory
     * @param  int $mode
     * @return static
     */
    public function uploadDirectory($sourceDirectory, $targetDirectory, $mode = FTP_BINARY)
    {
        $d = dir($sourceDirectory);

        // do this for each file in the directory
        while ($file = $d->read()) {

            // to prevent an infinite loop
            if ($file != "." && $file != "..") {

                // do the following if it is a directory
                if (is_dir($sourceDirectory.'/'.$file)) {

                    if (!@$this->chdir($targetDirectory.'/'.$file)) {

                        // create directories that do not yet exist
                        $this->createDirectory($targetDirectory.'/'.$file);
                    }

                    // recursive part
                    $this->uploadDirectory(
                        $sourceDirectory.'/'.$file, $targetDirectory.'/'.$file,
                        $mode
                    );
                } else {

                    // put the files
                    $this->put(
                        $targetDirectory.'/'.$file, $sourceDirectory.'/'.$file,
                        $mode
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Returns a detailed list of files in the given directory.
     * @param  string       $directory The directory, by default is the current directory
     * @param  bool         $recursive
     * @return array
     * @throws FtpException
     */
    public function getList($directory = '.', $recursive = false)
    {
        if (!$this->isDirectory($directory)) {
            throw new FtpException('"'.$directory.'" is not a directory.');
        }

        $list  = ftp_rawlist($this->connection, $directory);
        $items = array();

        if($list === false) {
            return $items;
        }

        if (false == $recursive) {

            foreach ($list as $path => $item) {
                $chunks = preg_split("/\\s+/", $item);

                // if not "name"
                if (empty($chunks[8]) || $chunks[8] == '.' || $chunks[8] == '..') {
                    continue;
                }

                $path = $directory.'/'.$chunks[8];

                if(isset($chunks[9])) {
                    $nbChunks = count($chunks);

                    for ($i = 9; $i < $nbChunks; $i++) {
                        $path .= ' '.$chunks[$i];
                    }
                }


                if (substr($path, 0, 2) == './') {
                    $path = substr($path, 2);
                }

                $items[ $this->rawToType($item).'#'.$path ] = $item;
            }

            return $items;
        }

        foreach ($list as $item) {

            $len = strlen($item);

            if(!$len

            // "."
            || ($item[$len-1] == '.' && $item[$len-2] == ' '

            // ".."
            or $item[$len-1] == '.' && $item[$len-2] == '.' && $item[$len-3] == ' ')
            ){

                continue;
            }

            $chunks = preg_split("/\\s+/", $item);

            // if not "name"
            if (empty($chunks[8]) || $chunks[8] == '.' || $chunks[8] == '..') {
                continue;
            }

            $path = $directory.'/'.$chunks[8];

            if(isset($chunks[9])) {
                $nbChunks = count($chunks);

                for ($i = 9; $i < $nbChunks; $i++) {
                    $path .= ' '.$chunks[$i];
                }
            }

            if (substr($path, 0, 2) == './') {
                $path = substr($path, 2);
            }

            $items[$this->rawToType($item).'#'.$path] = $item;

            if ($item[0] == 'd') {
                $sublist = $this->getList($path, true);

                foreach ($sublist as $subpath => $subitem) {
                    $items[$subpath] = $subitem;
                }
            }
        }

        return $items;
    }

    /**
     * Parse raw list
     * @param  array $list
     * @return array
     */
    public function parseList(array $list)
    {
        $items = array();
        $path  = '';

        foreach ($list as $key => $child) {
            $chunks = preg_split("/\\s+/", $child);

            if (isset($chunks[8]) && ($chunks[8] == '.' or $chunks[8] == '..')) {
                continue;
            }

            if (count($chunks) === 1) {
                $len = strlen($chunks[0]);

                if ($len && $chunks[0][$len-1] == ':') {
                    $path = substr($chunks[0], 0, -1);
                }

                continue;
            }

            $item = [
                'permissions' => $chunks[0],
                'number'      => $chunks[1],
                'owner'       => $chunks[2],
                'group'       => $chunks[3],
                'size'        => $chunks[4],
                'month'       => $chunks[5],
                'day'         => $chunks[6],
                'time'        => $chunks[7],
                'name'        => $chunks[8],
                'type'        => $this->rawToType($chunks[0]),
            ];

            if ($item['type'] == 'link') {
                $item['target'] = $chunks[10]; // 9 is "->"
            }

            // if the key is not the path, behavior of ftp_rawlist() PHP function
            if (is_int($key) || false === strpos($key, $item['name'])) {
                array_splice($chunks, 0, 8);

                $key = $item['type'].'#'
                    .($path ? $path.'/' : '')
                    .implode(" ", $chunks);

                if ($item['type'] == 'link') {

                    // get the first part of 'link#the-link.ext -> /path/of/the/source.ext'
                    $exp = explode(' ->', $key);
                    $key = rtrim($exp[0]);
                }

                $items[$key] = $item;

            } else {

                // the key is the path, behavior of static::getList() method()
                $items[$key] = $item;
            }
        }

        return $items;
    }

    /**
     * Convert raw info (drwx---r-x ...) to type (file, directory, link, unknown).
     * Only the first char is used for resolving.
     * @param  string $permission Example : drwx---r-x
     * @throws FtpException
     * @return string The file type (file, directory, link, unknown)
     */
    public function rawToType($permission)
    {
        if (!is_string($permission)) {
            throw new FtpException('The "$permission" argument must be a string, "'
            .gettype($permission).'" given.');
        }

        if (empty($permission[0])) {
            return 'unknown';
        }

        switch ($permission[0]) {

            case '-':
                return 'file';

            case 'd':
                return 'directory';

            case 'l':
                return 'link';

            default:
                return 'unknown';
        }
    }

    /**
     * Forward the method call to FTP functions
     *
     * @param  string       $function
     * @param  array        $arguments
     * @return mixed
     * @throws FtpException When the function is not valid
     */
    public function __call($function, array $arguments)
    {
        if(method_exists($this, $function)) {
            return call_user_func_array(array($this, $function), $arguments);
        }

        $function = 'ftp_' . $function;
        if (function_exists($function)) {
            array_unshift($arguments, $this->connection);
            return call_user_func_array($function, $arguments);
        }

        throw new FtpException("{$function} is not a valid FTP function");
    }
}
