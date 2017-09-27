<?php

/**
 * @file
 * Contains \DrupalProject\composer\ScriptHandlerEzStyle.
 */

namespace ezCompany\composer;

use Composer\Script\Event;
use DrupalProject\composer\ScriptHandler;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandlerEzStyle extends ScriptHandler {

  protected static function getDrupalRoot($project_root) {
    return $project_root . '/htdocs';
  }

  public static function createRequiredFiles(Event $event) {
    $fs = new Filesystem();
    $root = static::getDrupalRoot(getcwd());
    $sites = $root . '/sites';
    $sites_dir = opendir($sites);

    while ($entry = readdir($sites_dir)) {
      if (strpos($entry, '.') !== 0 && is_dir($sites . '/' . $entry) && !in_array($entry, array('default'))) {
        static::prepareDirs($sites . '/' . $entry, $event);
      }
    }

    $dirs = [
      'modules',
      'profiles',
      'themes',
    ];

    // Required for unit testing
    foreach ($dirs as $dir) {
      if (!$fs->exists($root . '/'. $dir)) {
        $fs->mkdir($root . '/'. $dir);
        $fs->touch($root . '/'. $dir . '/.gitkeep');
      }
    }
  }

  protected static function prepareDirs($dir, Event $event) {
    $fs = new Filesystem();

    // Prepare the settings file for installation
    if (!$fs->exists($dir . '/settings.php') and $fs->exists($dir . '/../default/default.settings.php')) {
      $fs->copy($dir . '/../default/default.settings.php', $dir . '/settings.php');
      $fs->chmod($dir . '/settings.php', 0640);
      $event->getIO()->write("Create a $dir/settings.php file with chmod 0640");
    }

    // Prepare the services file for installation
    if (!$fs->exists($dir . '/services.yml') and $fs->exists($dir . '/../default/default.services.yml')) {
      $fs->copy($dir . '/../default/default.services.yml', $dir . '/services.yml');
      $fs->chmod($dir . '/services.yml', 0640);
      $event->getIO()->write("Create a $dir/services.yml file with chmod 0640");
    }

    // Create the files directory with chmod 0770
    if (!$fs->exists($dir . '/files')) {
      $oldmask = umask(0);
      $fs->mkdir($dir . '/files', 0770);
      umask($oldmask);
      $event->getIO()->write("Create a $dir/files directory with chmod 0770");
    }
  }

}
