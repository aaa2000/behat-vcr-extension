Feature: Usage VCR with Behat

  Background:
    Given a file named "index.php" with:
    """php
<?php

    echo 'Hello ' . ltrim($_SERVER['REQUEST_URI'], '/');
    """
    And a directory named "features/cassettes"
    And a file named "features/bootstrap/FeatureContext.php" with:
    """
      <?php

      use Behat\Behat\Context\Context;
      use BVCR\Behat\Context\VCRAwareContext;
      use VCR\Videorecorder;

      class FeatureContext implements Context, VCRAwareContext
      {
        private $videorecorder;
        private $response;

        public function setVideorecorder(Videorecorder $videorecorder)
        {
          $this->videorecorder = $videorecorder;
        }

        /**
         * @Given /^a request is made to "([^"]*)"$/
         */
        public function aRequestIsMadeTo($url)
        {
          $parsedUrl = parse_url($url);
          $newUrl = sprintf(
            '%s://%s:%s%s%s',
            $parsedUrl['scheme'],
            $parsedUrl['host'],
            getenv('BEHAT_VCR_WEBSERVER_PORT'),
            isset($parsedUrl['path']) ? $parsedUrl['path'] : '',
            isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : ''
          );
          $this->response = file_get_contents($newUrl);
        }

        /**
         * @Then /^the response should be "([^"]*)"$/
         */
         public function theResponseShouldBe($expectedResponse)
         {
            if ($this->response !== $expectedResponse) {
              throw new \Exception('Unexpected response: ' . $this->response);
            }
         }
      }
    """

  Scenario: Record HTTP interactions in a scenario by tagging it
    Given a file named "behat.yml" with:
    """
    # Use aliases to avoid duplicating configuration
    # https://github.com/cyklo/Bukkit-OtherBlocks/wiki/Aliases-%28advanced-YAML-usage%29
    aliases:
        - &VCRExtensionConfiguration
          cassette_path: "features/cassettes"
          cassette_storage: yaml
          library_hooks: ["curl", "soap", "stream_wrapper"]
          match_requests_on: ["method", "url", "host"]

    default:
        extensions:
            BVCR\Behat\ServiceContainer\VCRExtension:
                behat_tags:
                    - <<: *VCRExtensionConfiguration
                      tags: ["@localhost_request"]
                    - <<: *VCRExtensionConfiguration
                      tags: ["@disallowed_1", "@disallowed_2"]
                      mode: "none"
                    - <<: *VCRExtensionConfiguration
                      tags: ["@vcr"]
                      use_scenario_name: true
    """
    And a file named "features/vcr_example.feature" with:
    """
      Feature: VCR example

        @localhost_request
        Scenario: tagged scenario
          When a request is made to "http://localhost:7777/localhost_request"
          Then the response should be "Hello localhost_request"
          #When a request is made to "http://localhost:7777/nested_cassette" within a cassette named "nested_cassette.yml"
          #Then the response should be "Hello nested_cassette"
          When a request is made to "http://localhost:7777/another_request"
          Then the response should be "Hello another_request"
    """
    When I run "behat features/vcr_example.feature"
    Then it should pass with:
    """
    1 scenario (1 passed)
    """
    And the file "features/cassettes/behat_tags/localhost_request.yml" should contain "Hello localhost_request"
    And the file "features/cassettes/behat_tags/localhost_request.yml" should contain "Hello another_request"
    # Run again without the server; we'll get the same responses because VCR
    # will replay the recorded responses.
    And I stop the web server
    When I run "behat features/vcr_example.feature"
    Then it should pass with:
    """
    1 scenario (1 passed)
    """
    And the file "features/cassettes/behat_tags/localhost_request.yml" should contain "Hello localhost_request"
    And the file "features/cassettes/behat_tags/localhost_request.yml" should contain "Hello another_request"

  Scenario: Replay HTTP interactions from a scenario tagged
    Given a file named "behat.yml" with:
    """
    default:
        extensions:
            BVCR\Behat\ServiceContainer\VCRExtension:
                behat_tags:
                    - tags: ["@localhost_request"]
                      cassette_path: "features/cassettes"
                      cassette_storage: yaml
                      library_hooks: ["curl", "soap", "stream_wrapper"]
                      match_requests_on: ["method", "url", "host"]
    """
    And a file named "features/vcr_example.feature" with:
    """
      Feature: VCR example

        @localhost_request
        Scenario: tagged scenario
          When a request is made to "http://localhost:7777/localhost_request"
          Then the response should be "Hello localhost_request"
    """
    And I run "behat features/vcr_example.feature"
    When I stop the web server
    And I rerun "behat features/vcr_example.feature"
    Then it should pass

  Scenario: No record HTTP interactions in a scenario not tagging with a VCR behat tag
    Given a file named "behat.yml" with:
    """
    default:
        extensions:
            BVCR\Behat\ServiceContainer\VCRExtension:
                behat_tags:
                    - tags: ["@localhost_request"]
                      cassette_path: "features/cassettes"
                      cassette_storage: yaml
                      library_hooks: ["curl", "soap", "stream_wrapper"]
                      match_requests_on: ["method", "url", "host"]
    """
    And a file named "features/vcr_example.feature" with:
    """
      Feature: VCR example

        Scenario: No tagged scenario
          When a request is made to "http://localhost:7777/localhost_request"
          Then the response should be "Hello localhost_request"
    """
    When I run "behat features/vcr_example.feature"
    Then it should pass with:
    """
    1 scenario (1 passed)
    """
    And the file "features/cassettes/behat_tags/localhost_request.yml" does not exist

  Scenario: Record HTTP interactions using scenario name should create a cassette named <feature_name>/<scenario_name>
    Given a file named "behat.yml" with:
    """
    default:
        extensions:
            BVCR\Behat\ServiceContainer\VCRExtension:
                behat_tags:
                    - tags: ["@localhost_request"]
                      cassette_path: "features/cassettes"
                      cassette_storage: yaml
                      library_hooks: ["curl", "soap", "stream_wrapper"]
                      match_requests_on: ["method", "url", "host"]
                      use_scenario_name: true
    """
    And a file named "features/vcr_example.feature" with:
    """
      Feature: VCR example

        @localhost_request
        Scenario: tagged scenario
          When a request is made to "http://localhost:7777/localhost_request"
          Then the response should be "Hello localhost_request"
    """
    When I run "behat features/vcr_example.feature"
    Then it should pass with:
    """
    1 scenario (1 passed)
    """
    And the file "features/cassettes/vcr_example/tagged_scenario.yml" should contain "Hello localhost_request"

  Scenario: Record HTTP interactions using scenario name for scenario outlines should create a cassette named <feature_name>/<scenario_name>/<row_name>
    Given a file named "behat.yml" with:
    """
    default:
        extensions:
            BVCR\Behat\ServiceContainer\VCRExtension:
                behat_tags:
                    - tags: ["@localhost_request"]
                      cassette_path: "features/cassettes"
                      cassette_storage: yaml
                      library_hooks: ["curl", "soap", "stream_wrapper"]
                      match_requests_on: ["method", "url", "host"]
                      use_scenario_name: true
    """
    And a file named "features/vcr_example.feature" with:
    """
      Feature: VCR example

        @localhost_request
        Scenario Outline: tagged scenario outline
          When a request is made to "http://localhost:7777/<value>"
          Then the response should be "Hello <value>"

          Examples:
            | key  | value |
            | foo  | bar   |
            | baz  | qux   |
    """
    When I run "behat features/vcr_example.feature"
    Then it should pass with:
    """
    2 scenarios (2 passed)
    """
    And the file "features/cassettes/vcr_example/tagged_scenario_outline/_foo_bar_.yml" should contain "Hello bar"
    And the file "features/cassettes/vcr_example/tagged_scenario_outline/_baz_qux_.yml" should contain "Hello qux"

  Scenario: Replay HTTP interactions from cassette named via the scenario name
    Given a file named "behat.yml" with:
    """
    default:
        extensions:
            BVCR\Behat\ServiceContainer\VCRExtension:
                behat_tags:
                    - tags: ["@localhost_request"]
                      cassette_path: "features/cassettes"
                      cassette_storage: yaml
                      library_hooks: ["curl", "soap", "stream_wrapper"]
                      match_requests_on: ["method", "url", "host"]
                      use_scenario_name: true
    """
    And a file named "features/vcr_example.feature" with:
    """
      Feature: VCR example

        @localhost_request
        Scenario: tagged scenario
          When a request is made to "http://localhost:7777/localhost_request"
          Then the response should be "Hello localhost_request"
    """
    And I run "behat features/vcr_example.feature"
    When I stop the web server
    And I rerun "behat features/vcr_example.feature"
    Then it should pass

    
