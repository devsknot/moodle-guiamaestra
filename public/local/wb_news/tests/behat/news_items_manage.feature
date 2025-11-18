@local @local_wb_news @javascript

Feature: Test management of the wb_news instance items.
  In order to see news instance items
  as a student
  I need to create a news instance items and populate it with news as admin

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
    And the following "local_wb_news > news instances" exist:
      | name      | template                   | columns | contexts                |
      | Instance1 | local_wb_news/wb_news_grid | 4       | System wide, Category 1 |

  @javascript
  Scenario: News: Add and edit instance items via UI
    Given the following "user private files" exist:
      | user     | filepath                                      | filename         |
      | admin    | local/wb_news/tests/fixtures/image_sample.png | image_sample.png |
      | admin    | local/wb_news/tests/fixtures/icon_sample.png  | icon_sample.png  |
    And I log in as "admin"
    When I visit "/local/wb_news/index.php"
    ## Add the instance item
    And I click on ".wb-news-addeditbutton.fa-plus" "css_element"
    And I wait "1" seconds
    And I set the field "Headline" to "Simple Headline"
    And I set the field "Subheadline" to "Simple Subheadline"
    And I set the field "Description" to "Simple Description"
    And the field "Lower comes first" matches value "1"
    And I press "Save changes"
    And I wait "1" seconds
    ## Validate simple instance item
    Then I should see "Instance1" in the "[data-id=\"wb-news-all-instances-container\"]" "css_element"
    ##And I click on "button[data-target^='#instance-']" "css_element"
    And I should see "Simple Headline" in the ".wb-news-container" "css_element"
    And I should see "Simple Subheadline" in the ".wb-news-container" "css_element"
    And I should see "Simple Description" in the ".wb-news-container" "css_element"
    ## Duplicate the instance item and set an advanced fields
    And I click on ".wb-news-container .wb-news-copybutton.fa-copy" "css_element"
    And I wait "1" seconds
    And the field "Headline" matches value "Simple Headline"
    And the field "Lower comes first" matches value "2"
    And I set the field "Headline" to "Adv Headline"
    And I set the field "Subheadline" to "Adv Subheadline"
    And I set the field "Description" to "Adv Description"
    And I set the field "Main image alt text" to "I1 adv image alt text"
    And I set the field "Icon alt text" to "I1 adv icon alt text"
    And I set the field "Button Link" to "/my/courses.php"
    And I set the field "Button link attributes" to "Opens the link in a new tab or window"
    And I set the field "Button Text" to "NewsMyCourses"
    And I set the field "Key: Value" to "Key1:Value1"
    And I set the field "Tags" to "Tag1,Tag2"
    ## Add image and icon
    And I click on "Add..." "button" in the "Main Image" "form_row"
    And I click on "Private files" "link" in the ".moodle-dialogue-base[aria-hidden='false'] .fp-repo-area" "css_element"
    And I click on "image_sample.png" "link"
    And I click on "Select this file" "button"
    And I click on "Add..." "button" in the "Icon" "form_row"
    # For 2nd dialog - it is necessary to to use aria-hidden='false'
    And I click on "Private files" "link" in the ".moodle-dialogue-base[aria-hidden='false'] .fp-repo-area" "css_element"
    And I click on "icon_sample.png" "link" in the ".moodle-dialogue-base[aria-hidden='false'] .fp-content" "css_element"
    And I click on "Select this file" "button" in the ".moodle-dialogue-base[aria-hidden='false'] .moodle-dialogue-content" "css_element"
    And I press "Save changes"
    And I wait "1" seconds
    ## Validate advanced instance item
    And I should see "Adv Headline" in the ".wb-news-container" "css_element"
    And I should see "Adv Subheadline" in the ".wb-news-container" "css_element"
    And I should see "Adv Description" in the ".wb-news-container" "css_element"
    And the image at "//div[contains(@class, 'wb-news-container')]//img[contains(@class, 'wb_news-headerimage') and contains(@src, 'pluginfile.php') and contains(@src, '/local_wb_news/bgimage/') and @alt='I1 adv image alt text']" "xpath_element" should be identical to "local/wb_news/tests/fixtures/image_sample.png"
    And the image at "//div[contains(@class, 'wb-news-container')]//img[contains(@class, 'card-icon') and contains(@src, 'pluginfile.php') and contains(@src, '/local_wb_news/icon/') and @alt='I1 adv icon alt text']" "xpath_element" should be identical to "local/wb_news/tests/fixtures/icon_sample.png"
    ## Delete simple instance item (1st one)
    And I click on ".wb-news-container .wb-news-deletebutton.fa-trash" "css_element"
    And I should see "Confirm deletion of this news item" in the ".modal.show .modal-header" "css_element"
    And I press "Save changes"
    And I should not see "Simple Headline" in the ".wb-news-container" "css_element"
    ## Check link
    And I click on "NewsMyCourses" "link" in the ".wb-news-container" "css_element"
    And I should see "My courses"

  @javascript
  Scenario: News: Add and edit instance items via DB
    Given the following "local_wb_news > news items" exist:
      | instance  | headline  | description  | bgimagefilepath                               | bgimagetext  | iconfilepath                                 | icontext    |
      | Instance1 | HeadNews1 | Description1 | local/wb_news/tests/fixtures/image_sample.png | N1 image alt |                                              |             |
      | Instance1 | HeadNews2 | Description2 |                                               |              | local/wb_news/tests/fixtures/icon_sample.png | N2 icon alt |
    And I log in as "admin"
    When I visit "/local/wb_news/index.php"
    Then I should see "Instance1" in the "[data-id=\"wb-news-all-instances-container\"]" "css_element"
    And I click on "button[data-target^='#instance-']" "css_element"
    And I should see "HeadNews1" in the ".wb-news-container" "css_element"
    And I should see "HeadNews2" in the ".wb-news-container" "css_element"
    And I should see "Description1" in the ".wb-news-container" "css_element"
    And I should see "Description2" in the ".wb-news-container" "css_element"
    And the image at "//div[contains(@class, 'wb-news-container')]//img[contains(@class, 'wb_news-headerimage') and contains(@src, 'pluginfile.php') and contains(@src, '/local_wb_news/bgimage/') and @alt='N1 image alt']" "xpath_element" should be identical to "local/wb_news/tests/fixtures/image_sample.png"
    And the image at "//div[contains(@class, 'wb-news-container')]//img[contains(@class, 'card-icon') and contains(@src, 'pluginfile.php') and contains(@src, '/local_wb_news/icon/') and @alt='N2 icon alt']" "xpath_element" should be identical to "local/wb_news/tests/fixtures/icon_sample.png"

  @javascript
  Scenario: News: Add instance items via DB and view them as student on Moodle page
    Given the following "activities" exist:
      | activity | name       | intro      | course | idnumber |
      | page     | PageNews1  | PageDesc1  | C1     | PAGE1    |
    And the following "local_wb_news > news items" exist:
      | instance  | headline  | description  | bgimagefilepath                               | bgimagetext  | iconfilepath                                 | icontext    |
      | Instance1 | HeadNews1 | Description1 | local/wb_news/tests/fixtures/image_sample.png | N1 image alt |                                              |             |
      | Instance1 | HeadNews2 | Description2 |                                               |              | local/wb_news/tests/fixtures/icon_sample.png | N2 icon alt |
    And News instance "Instance1" has been added to the Page resource "PageNews1"
    And I am on the "PageNews1" "page activity" page logged in as student1
    And I should see "HeadNews1" in the ".wb-news-container" "css_element"
    And I should see "HeadNews2" in the ".wb-news-container" "css_element"
    And I should see "Description1" in the ".wb-news-container" "css_element"
    And I should see "Description2" in the ".wb-news-container" "css_element"
    And the image at "//div[contains(@class, 'wb-news-container')]//img[contains(@class, 'wb_news-headerimage') and contains(@src, 'pluginfile.php') and contains(@src, '/local_wb_news/bgimage/') and @alt='N1 image alt']" "xpath_element" should be identical to "local/wb_news/tests/fixtures/image_sample.png"
    And the image at "//div[contains(@class, 'wb-news-container')]//img[contains(@class, 'card-icon') and contains(@src, 'pluginfile.php') and contains(@src, '/local_wb_news/icon/') and @alt='N2 icon alt']" "xpath_element" should be identical to "local/wb_news/tests/fixtures/icon_sample.png"
