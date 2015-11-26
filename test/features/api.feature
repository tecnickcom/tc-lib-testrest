Feature: Testing if the API is responding

Scenario: Test JSON data
    Given that the request body is valid JSON
    """
    {
        "alpha":"beta",
        "gamma":"delta",
        "count":3,
        "collection":["a","b","c"]
    }
    """
    When I make a "POST" request to "/"
    Then the response status code should be "200"
    And the "success" property equals "true"

Scenario: Simple test that show most options
    Given that header property "Test" is "12345"
    And that "property[0].name" is "12345"
    # NOTE: The following loads data from a JSON file and converts it into property-value items
    Given that the properties in the "JSON"
    """
    {
        "alpha":"beta",
        "gamma":"delta",
        "count":3,
        "collection":["a","b","c"]
    }
    """
    ## The following loads data from a JSON file and merge it with the existing one
    Given that the properties are imported from the JSON file "test/resources/data.json"
    When I make a "GET" request to "/"
    Then echo last response
    Then wait "1" second
    Then the response status code should be "200"
    And the "Connection" header property equals "close"
    And the value of the "Connection" header property matches the pattern "/^[a-z]+$/"
    And the response is JSON
    And the response has a "success" property
    And the type of the "success" property should be "boolean"
    And the "success" property equals "true"
    And the value of the "datetime" property matches the pattern "/^[0-9]{4}[\-][0-9]{2}[\-][0-9]{2} [0-9]{2}[:][0-9]{2}[:][0-9]{2}$/"
    And the response body matches the pattern "/[\"]ga[m]{2}a[\"]/"
    And the "data" property is an "object" with "10" items
    And the "data.property" property is an "array" with "1" item
    And the "data.property[0].name" property equals "12345"
    And the "data.collection" property is an "array" with "3" items
    And the length of the "data.gamma" property should be "5"
    And the "data.alpha" property equals "beta"
    And the "data.gamma" property equals "delta"
    And the "data.count" property equals "3"
    And the "data.string" property equals "one two"
    And the "data.integer" property equals "123"
    And the "data.float" property equals "1.2345"
    And the "data.boolean" property equals "true"
    And the "data.array" property is an "array" with "4" items
    And the response body contains the JSON data
        """
        {
            "success":true,
            "data":{
                "property":[{"name":"12345"}],
                "alpha":"beta",
                "gamma":"delta",
                "count":"3",
                "collection":["a","b","c"],
                "string":"one two",
                "integer":"123",
                "float":"1.2345",
                "boolean":"1",
                "array":["a","b","c","d"]
            },
            "raw":""
        }
        """

Scenario Outline: Test data table mode
    Given that "property.name" is "<name>"
    When I make a "GET" request to "/"
    Then the response status code should be "<code>"
    And the response is JSON
    And the "success" property equals "<success>"
    And the "data.property.name" property equals "<name>"
    Examples:
        | name  | code | success |
        | alpha |  200 | true    |
        | bravo |  200 | true    |

Scenario: Test Raw data
    Given that the request body is
    """
    {
        "alpha":"beta",
        "gamma":"delta",
        "count":3,
        "collection":["a","b","c"]
    }
    """
    When I make a "POST" request to "/"
    Then the response status code should be "200"
    And the "success" property equals "true"
    And the response has a "raw" property

Scenario: Test RAW input file data
    ## load RAW data from a file
    Given that the request body is imported from the file "test/resources/data.json"
    When I make a "POST" request to "/"
    Then the response status code should be "200"
    And the "success" property equals "true"
    And the response has a "raw" property

Scenario: Test input properties in tabular form
    Given that the properties in the "TABLE"
        | name        | Nicola           |
        | email       | name@example.com |
    When I make a "GET" request to "/"
    Then the response status code should be "200"
    And the "success" property equals "true"
    And the "data.name" property equals "Nicola"
    And the "data.email" property equals "name@example.com"
    And the response has a "raw" property

