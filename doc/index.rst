Behat VCR Extension
===================

Installation
------------

This extension requires:

* Behat 3.0+
* php-vcr 1.2.4+
* PHP 5.4+

Through Composer
~~~~~~~~~~~~~~~~

The easiest way to keep your suite updated is to use `Composer <http://getcomposer.org>`_:

1. Install:

    .. code-block:: bash

        $ composer require --dev bvcr/behat-vcr-extension

3. Activate extension by specifying its class in your ``behat.yml``:

    .. code-block:: yaml

        # behat.yml
        default:
            # ...
            extensions:
                BVCR\Behat\ServiceContainer\VCRExtension:
                    behat_tags:
                        - # Behat tags to tell VCR to use a cassette for the tagged scenario
                          tags: ["@localhost_request"]

                          # Path where cassette files should be stored
                          cassette_path: "features/cassettes"
                          
                          # Format in which is stored the cassette
                          cassette_storage: yaml
                          
                          # Enable only some library hooks
                          library_hooks: ["curl", "soap", "stream_wrapper"]
                          
                          # Customize how VCR matches requests
                          match_requests_on: ["method", "url", "host"]
                          
                          # Record mode determines how requests are handled
                          mode: "once"
                          
                          # VCR cassette filenaming strategy
                          # One of "by_scenario_name"; "by_tags"
                          cassette_filenaming_strategy:  "by_scenario_name"
                                                    
See http://php-vcr.github.io/documentation/configuration/

Usage
-----
