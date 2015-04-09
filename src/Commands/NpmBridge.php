<?php
namespace Flint\Commands;

use Composer\Script\Event,
    Composer\Installer\InstallerEvent,
    Composer\IO\IOInterface,
    Composer\Util\ProcessExecutor;

class NpmBridge
{
    private static function checkNpmExists(IOInterface $io)
    {
        $returnVal = shell_exec("which npm");

        if ($returnVal === null) {
            $io->writeError("\033[41mCan't find `npm` in \$PATH, check `npm` and `node` are installed.\033[0m");
            exit(1);
        }
    }

    private static function installDependencies(IOInterface $io, $folder)
    {
        $io->write("\033[0;32mInstalling front end dependencies from package.json\033[0m");
        $proc = new ProcessExecutor();
        $proc->execute('cd '.$folder.' && npm install');
        $io->write("\033[0;32mFront end dependencies installed\033[0m");
    }

    public static function postInstallCmd(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $baseDir = __DIR__ . "/../../web";

        self::checkNpmExists($io);
        self::installDependencies($io, $baseDir);

    }
}
