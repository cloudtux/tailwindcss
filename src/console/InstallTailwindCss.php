<?php

namespace Cloudtux\TailwindCss\console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class InstallTailwindCss extends Command
{

    protected $signature = 'install:tailwindcss';
    protected $description = 'Install TailwindCss preset';
    private $packageManager;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $this->getPackageManager();
        $this->createTailwindJsFile();
        $this->updateSassFile();
        $this->updateWebpackFile();
        $this->installComplete();

    }

    private function getPackageManager(){

        $this->packageManager = $this->choice('How would you like to install?', ['yarn', 'npm'], 0);

        $this->info("Installing with " . $this->packageManager . ". Please wait...");

        if($this->packageManager == 'yarn'){
            return $this->installUsingYarn();
        }

        return $this->installUsingNpm();

    }

    private function installUsingYarn(){

        $process = new Process('yarn add tailwindcss --dev');
        $process->run();
        return $this->info($process->getOutput());

    }

    private function installUsingNpm(){

        $process = new Process('npm install tailwindcss --save-dev');
        $process->run();
        return $this->info($process->getOutput());

    }

    private function createTailwindJsFile(){

        $this->info("Creating tailwind.js in your root directory.");

        $process = new Process('./node_modules/.bin/tailwind init tailwind.js');
        $process->run();
        return $this->info($process->getOutput());

    }

    private function updateSassFile(){

        $appCssFile = base_path('resources/sass/app.scss');

        if (!file_exists($appCssFile)) {
            $this->info("Creating file /resources/sass/app.scss ...");
            $process = new Process('touch ' . $appCssFile);
            $process->run();
        }

        $this->info("Appending tailwind directives to /resources/sass/app.scss ...");

        $fileContents = file_get_contents($appCssFile);

        if (!preg_match("/Tailwind Directives/", $fileContents)) {
            $fileContents .= "\n\n//Tailwind Directives\n\n";
        }

        if (!preg_match("/@tailwind preflight;/", $fileContents)) {
            $fileContents .= "@tailwind preflight;\n";
        }

        if (!preg_match("/@tailwind components;/", $fileContents)) {
            $fileContents .= "@tailwind components;\n";
        }

        if (!preg_match("/@tailwind utilities;/", $fileContents)) {
            $fileContents .= "@tailwind utilities;";
        }

        file_put_contents($appCssFile, $fileContents);

    }

    private function installComplete(){

        $command = ($this->packageManager == 'yarn' ? 'yarn run dev' : 'npm run dev');

        $this->info("Installation is now complete!");
        $process = new Process($command);
        $process->run();

    }

    private function updateWebpackFile(){

        $webpackFile = base_path('webpack.mix.js');

        if (!file_exists($webpackFile)) {
            $this->info("Creating file webpack.mix.js ...");
            $process = new Process('touch ' . $webpackFile);
            $process->run();
        }

        $this->info("Appending tailwind to webpack.mix.js ...");

        $fileContents = file_get_contents($webpackFile);

        $sassContents = ".sass('resources/sass/app.scss', 'public/css')\n\t";
        $sassContents .= ".options({\n\t\t";
        $sassContents .= "processCssUrls: false,\n\t\t";
        $sassContents .= "postCss: [ tailwindcss('tailwind.js') ],\n\t";
        $sassContents .= "});";

        $prependFileContents = "";

        if (!preg_match("/require\(\'tailwindcss\'\)/", $fileContents)) {

            if (preg_match("/.sass(.*)/", $fileContents)) {
                $fileContents = preg_replace("/.sass(.*)/", $sassContents, $fileContents);
            } else {
                $fileContents .= "mix" . $sassContents;
            }

            $prependFileContents = "const tailwindcss = require('tailwindcss');\n";
        }

        file_put_contents($webpackFile, $prependFileContents . $fileContents);

    }

}