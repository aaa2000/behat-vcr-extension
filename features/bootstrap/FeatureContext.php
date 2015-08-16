<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Cjm\Behat\LocalWebserverExtension\Webserver;

class FeatureContext implements Context, SnippetAcceptingContext
{
    /**
     * @var string
     */
    private $phpBin;
    /**
     * @var Process
     */
    private $process;
    /**
     * @var string
     */
    private $workingDir;
    /**
     * @var integer
     */
    private $port;
    /**
     * @var \Cjm\Behat\LocalWebserverExtension\Webserver\WebserverController
     */
    private $server;

    public function __construct($port = 8000)
    {
        $this->port = $port;
    }

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     * @AfterSuite
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat-vcr')) {
            self::clearDirectory($dir);
        }
    }

    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);
        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($path);
    }

    /**
     * Prepares test folders in the temporary directory.
     *
     * @BeforeScenario
     */
    public function prepareScenario()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat-vcr' . DIRECTORY_SEPARATOR .
            md5(microtime() * rand(0, 10000));
        mkdir($dir . '/features/bootstrap', 0777, true);
        mkdir($dir . '/tests/fixtures', 0777, true);
        $phpFinder = new PhpExecutableFinder();
        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }
        $this->workingDir = $dir;
        $this->phpBin = $php;
        $this->process = new Process(null);
    }

    /**
     * Run webserver.
     *
     * The hooks are fired in the order in which they were defined inside your context.
     * So, this hook should be after prepareScenario method.
     *
     * @BeforeScenario
     */
    public function runWebServer()
    {
        $basicConfiguration = new Webserver\BasicConfiguration('localhost', $this->port, $this->workingDir);
        $this->server = new Webserver\BuiltInWebserverController($basicConfiguration);
        $this->server->startServer();
    }

    /**
     * @When I stop the web server
     */
    public function iStopWebserver()
    {
        $this->server->stopServer();
    }

    /**
     * Creates a directory with specified name.
     *
     * @Given /^(?:there is )?a directory named "([^"]*)"$/
     *
     * @param string $directory name of the directory (relative path)
     */
    public function aDirectoryNamed($directory)
    {
        $this->createDirectory($this->workingDir . '/' . $directory);
    }

    private function createDirectory($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * Creates a file with specified name and context in current working dir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     *
     * @param string $filename name of the file (relative path)
     * @param PyStringNode $content PyString string instance
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    private function createFile($filename, $content)
    {
        $path = dirname($filename);
        $this->createDirectory($path);
        file_put_contents($filename, $content);
    }

    /**
     * Runs behat command with provided parameters
     *
     * @When /^I run "behat(?: ((?:\"|[^"])*))?"$/
     * @When /^I rerun "behat(?: ((?:\"|[^"])*))?"$/
     *
     * @param string $argumentsString
     */
    public function iRunBehat($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, array('\'' => '"'));

        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->setCommandLine(
            sprintf(
                '%s %s %s %s',
                $this->phpBin,
                escapeshellarg(BEHAT_BIN_PATH),
                $argumentsString,
                strtr('--format-settings=\'{"timer": false}\' --no-colors', array('\'' => '"', '"' => '\"'))
            )
        );

        $env = $this->process->getEnv();
        $env['BEHAT_VCR_WEBSERVER_PORT'] = $this->port;
        $this->process->setEnv($env);

        $this->process->start();
        $this->process->wait();
    }

    /**
     * Checks whether previously runned command passes|failes with provided output.
     *
     * @Then /^it should (fail|pass) with:$/
     *
     * @param string $success "fail" or "pass"
     * @param PyStringNode $text PyString text instance
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        $this->itShouldFail($success);
        $this->theOutputShouldContain($text);
    }

    /**
     * Checks whether previously run command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
     *
     * @param string $success "fail" or "pass"
     */
    public function itShouldFail($success)
    {
        if ('fail' === $success) {
            if (0 === $this->getExitCode()) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }

            PHPUnit_Framework_Assert::assertNotEquals(0, $this->getExitCode());
        } else {
            if (0 !== $this->getExitCode()) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }

            PHPUnit_Framework_Assert::assertEquals(0, $this->getExitCode());
        }
    }

    private function getExitCode()
    {
        return $this->process->getExitCode();
    }

    private function getOutput()
    {
        $output = $this->process->getErrorOutput() . $this->process->getOutput();

        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        return trim(preg_replace("/ +$/m", '', $output));
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @Then the output should contain:
     *
     * @param PyStringNode $text PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        PHPUnit_Framework_Assert::assertContains($this->getExpectedOutput($text), $this->getOutput());
    }

    private function getExpectedOutput(PyStringNode $expectedText)
    {
        $text = strtr($expectedText, array('\'\'\'' => '"""'));

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback(
                '/ features\/[^\n ]+/', function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                }, $text
            );
            $text = preg_replace_callback(
                '/\<span class\="path"\>features\/[^\<]+/', function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                }, $text
            );
            $text = preg_replace_callback(
                '/\+[fd] [^ ]+/', function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                }, $text
            );
        }

        return $text;
    }

    /**
     * @Given the directory :directory does not exist
     * @Then the file :directory does not exist
     *
     * @param string $directory Directory path or file path
     */
    public function theDirectoryDoesNotExist($directory)
    {
        PHPUnit_Framework_Assert::assertThat(
            $directory,
            PHPUnit_Framework_Assert::logicalNot(new PHPUnit_Framework_Constraint_FileExists())
        );
    }

    /**
     * @Then the file :path should contain :text
     */
    public function theFileShouldContain($path, $text)
    {
        $path = $this->workingDir . '/' . $path;
        PHPUnit_Framework_Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));

        PHPUnit_Framework_Assert::assertContains($text, $fileContent);
    }
}
