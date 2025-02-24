<?php

namespace Test\Managers;

use PHPUnit\Framework\Exception;

class FileManager
{
    private readonly string $repository;
    private readonly string $remoteRepository;

    public function __construct()
    {
        $this->repository = $this->createDirectory();
        $this->remoteRepository = $this->createDirectory();
    }

    public function createDirectory(): string
    {
        $tempfile = tempnam(sys_get_temp_dir(), "git_");

        if (file_exists($tempfile)) {
            unlink($tempfile);
        }

        mkdir($tempfile);

        if (!is_dir($tempfile)) {
            throw new Exception("Folder hasn't been created");
        }

        return $tempfile;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function getRemoteRepository(): string
    {
        return $this->remoteRepository;
    }


    /**
     * Create multiple files
     *
     * @param array<int, string> $files
     * @return void
     */
    public function createMultiple(array $files): void
    {
        foreach ($files as $file) {
            file_put_contents($file, '<?php');
        }
    }

    public function create(string $file, ?string $repository = null): void
    {
        $repository = $repository ?? $this->repository;



        $path = $repository . DIRECTORY_SEPARATOR . $file;
        file_put_contents($path, '<?php');
    }

    public function editFile(string $file, ?string $repository = null): void
    {
        $repository = $repository ?? $this->repository;

        $file = $repository . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($file)) {
            throw new \Exception('File does not exist');
        }

        // check if file exist fix
        file_put_contents($file, '<?php echo 111; ?>');
    }

    /**
     * @param string $dir
     * @return bool
     * @throws \Exception
     */
    public function removeDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            throw new \Exception('Directory does not exist');
        }

        $items = array_diff(scandir($dir), ['.', '..']);

        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    public function removeTestDirectories(): void
    {
        $this->removeDirectory($this->repository);
        $this->removeDirectory($this->remoteRepository);
    }
}
