Feature: Testing the example entry point

Scenario: Simple test that show exact-match features
    And that "name" is "beautiful"
    When I make a "GET" request to "/example.php"
    Then the response status code should be "200"
    # check all JSON fields
    And the response body JSON equals
        """
        {
            "raw":"",
            "data":{
                "name":"beautiful"
            },
            "success":true
        }
        """
    # Exact string match:
    Then the response body equals
        """
        {"success":true,"data":{"name":"beautiful"},"raw":""}
        """
