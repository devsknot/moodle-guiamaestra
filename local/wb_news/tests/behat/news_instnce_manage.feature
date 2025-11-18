@local @local_wb_news @javascript

Feature: Test management of the wb_news instance.
  In order to see news instance
  I need to create a news instance and populate it with news as admin

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | Student   | 1        |
      | student2 | Student   | 2        |
      | teacher  | Teacher   | 3        |
      | manager  | Manager   | 4        |
    And the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    And the following "courses" exist:
      | category | fullname | shortname |
      | CAT1     | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student1 | C1     | student        |
      | teacher  | C1     | editingteacher |

  @javascript
  Scenario: News: Add and edit an instance via UI
    Given I log in as "admin"
    And I visit "/local/wb_news/index.php"
    ## Add News instance
    And I click on "Add instance" "text"
    And I wait "1" seconds
    And I set the field "Name" to "Instance 1"
    And I set the field "Template" to "Grid template"
    And I set the field "Columns" to "6"
    And I expand the "Allow editing in ..." autocomplete
    And I click on "Cat 1" "text" in the "//div[contains(@id, 'fitem_id_contextids_')]//ul[contains(@class, 'form-autocomplete-suggestions')]" "xpath_element"
    And I click on "Category 1" "text" in the "//div[contains(@id, 'fitem_id_contextids_')]//ul[contains(@class, 'form-autocomplete-suggestions')]" "xpath_element"
    When I press "Save changes"
    And I wait "1" seconds
    ## View the instance
    Then I should see "Instance 1" in the "[data-id=\"wb-news-all-instances-container\"]" "css_element"
    ## Validate grid template
    And I click on "button[data-target^='#instance-']" "css_element"
    And "//div[contains(@class, 'wb-news-container')]" "xpath_element" should exist
    ## Edit the instance
    And I click on ".wb-news-addeditbutton.fa-edit" "css_element"
    And I wait "1" seconds
    And I set the field "Name" to "News instance 1"
    And the field "Columns" matches value "6"
    And I should see "Cat 1" in the "//div[contains(@id, 'fitem_id_contextids_')]//div[contains(@class, 'form-autocomplete-multiple')]" "xpath_element"
    And I should see "Category 1" in the "//div[contains(@id, 'fitem_id_contextids_')]//div[contains(@class, 'form-autocomplete-multiple')]" "xpath_element"
    And I set the field "Template" to "Tabs template"
    And I press "Save changes"
    And I wait "1" seconds
    ## View updated instance
    Then I should see "News instance 1" in the "[data-id=\"wb-news-all-instances-container\"]" "css_element"
    ## Validate tab template
    And I click on "button[data-target^='#instance-']" "css_element"
    And "//ul[contains(@id, 'wb_news_tab-')]" "xpath_element" should exist

  @javascript
  Scenario: News: Add instance via DB, edit and delete it via UI
    Given the following "local_wb_news > news instances" exist:
      | name       | template                   | columns | contexts                |
      | Instance 0 | local_wb_news/wb_news_grid | 2       | System wide, Category 1 |
    And I log in as "admin"
    When I visit "/local/wb_news/index.php"
    ## View the instance
    Then I should see "Instance 0" in the "[data-id=\"wb-news-all-instances-container\"]" "css_element"
    ## Edit the instance
    And I click on ".wb-news-addeditbutton.fa-edit" "css_element"
    And I wait "1" seconds
    And I set the field "Name" to "News instance 0"
    And I set the field "Template" to "Tabs template"
    And I should see "System wide" in the "//div[contains(@id, 'fitem_id_contextids_')]//div[contains(@class, 'form-autocomplete-multiple')]" "xpath_element"
    And I should see "Category 1" in the "//div[contains(@id, 'fitem_id_contextids_')]//div[contains(@class, 'form-autocomplete-multiple')]" "xpath_element"
    And I press "Save changes"
    And I wait "1" seconds
    ## View updated instance
    Then I should see "News instance 0" in the "[data-id=\"wb-news-all-instances-container\"]" "css_element"
    ## Validate tab template
    And I click on "button[data-target^='#instance-']" "css_element"
    And "//ul[contains(@id, 'wb_news_tab-')]" "xpath_element" should exist
    ## Delete the instance
    And I click on ".wb-news-deletebutton.fa-trash" "css_element"
    And I should see "Confirm deletion of this news item" in the ".modal.show .modal-header" "css_element"
    And I press "Save changes"
    And I should not see "News instance 0" in the "[data-id=\"wb-news-all-instances-container\"]" "css_element"
