@mod @mod_assign @assignfeedback @assignfeedback_offline
Feature: In an assignment, teachers can provide feedback using a spreadsheet downloaded
  from the grading screen. In order to provide feedback to students on their assignments
  As a teacher, I need to upload the completed spreadsheet back to the assignment.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | teacher |
      | student1 | C1 | student |
      | student2 | C1 | student |

  @javascript @_file_upload
  Scenario: Teachers should be able give all students grades using an offline worksheet.
    Given the following "activities" exist:
      | activity  | course  | name                  | assignsubmission_onlinetext_enabled  | assignfeedback_offline_enabled  |
      | assign    | C1      | Test assignment name  | 1                                    | 1                               |
    And the following "mod_assign > submissions" exist:
      | assign                | user      | onlinetext                  |
      | Test assignment name  | student1  | I'm the student1 submission |
      | Test assignment name  | student2  | I'm the student2 submission |
    And I am on the "Test assignment name" Activity page logged in as teacher1
    And I navigate to "Submissions" in current page administration
    When I click on "Actions" "link"
    And "Download grading worksheet" "link" should exist
    And following "Download grading worksheet" should download a file that:
      | Has mimetype  | text/csv                    |
      | Contains text | I'm the student1 submission |
      | Contains text | I'm the student2 submission |
    And following "Download grading worksheet" create a grade csv
    And I navigate to "Submissions" in current page administration
    When I click on "Actions" "link"
    And I click on "Upload grading worksheet" "link"
    And I upload "/mod/assign/feedback/offline/tests/fixtures/assignfeedback_offline_grading.csv" file to "Upload a file" filemanager
    And I press "id_submitbutton"
    Then I should see "Confirm changes in grading worksheet"
    And I should see "Set grade for Student 1 to 95.00"
    And I should see "Set grade for Student 2 to 72.00"
    And I press "id_submitbutton"
    Then I should see "Updated 2 grades and 0 feedback instances."
    And I press "Continue"
    And I should see "95.00"
    And I should see "72.00"

  @javascript @_file_upload
  Scenario: Students should be allowed to upload multiple attempts and view grades as they are graded.
    Given the following "activities" exist:
      | activity  | course  | name                  | assignsubmission_onlinetext_enabled  | assignfeedback_offline_enabled | assignfeedback_comments_enabled | attemptreopenmethod | maxattempts |
      | assign    | C1      | Test assignment name  | 1                                    | 1                              | 1                               | automatic           | -1          |
    And the following "mod_assign > submissions" exist:
      | assign                | user      | onlinetext                  |
      | Test assignment name  | student1  | I'm the student1 submission |
      | Test assignment name  | student2  | I'm the student2 submission |
    And I am on the "Test assignment name" Activity page logged in as teacher1
    And I navigate to "Submissions" in current page administration
    When I click on "Actions" "link"
    And "Download grading worksheet" "link" should exist
    And following "Download grading worksheet" should download a file that:
      | Has mimetype  | text/csv                    |
      | Contains text | I'm the student1 submission |
      | Contains text | I'm the student2 submission |
    And following "Download grading worksheet" create a grade and feedback csv
    And I navigate to "Submissions" in current page administration
    When I click on "Actions" "link"
    And I click on "Upload grading worksheet" "link"
    And I upload "/mod/assign/feedback/offline/tests/fixtures/assignfeedback_offline_grading.csv" file to "Upload a file" filemanager
    And I press "id_submitbutton"
    Then I should see "Confirm changes in grading worksheet"
    And I should see "Set grade for Student 1 to 95.00"
    And I should see "Set grade for Student 2 to 72.00"
    And I press "id_submitbutton"
    Then I should see "Updated 2 grades and 2 feedback instances."
    And I press "Continue"
    And I should see "95.00"
    And I should see "72.00"
    When I am on the "C1" "grades > Grader report > View" page
    Then I should see "Test assignment name" in the "user-grades" "table"
    And "Feedback provided" "icon" should exist in the "Student 1" "table_row"
    And "Feedback provided" "icon" should exist in the "Student 2" "table_row"

  @javascript @_file_upload
  Scenario: Teachers should be able to only give feedback as the first step of grading using an offline worksheet.
    Given the following "activities" exist:
      | activity  | course  | name                  | assignsubmission_onlinetext_enabled  | assignfeedback_offline_enabled | assignfeedback_comments_enabled | attemptreopenmethod | maxattempts |
      | assign    | C1      | Test assignment name  | 1                                    | 1                              | 1                               | automatic           | -1          |
    And the following "mod_assign > submissions" exist:
      | assign                | user      | onlinetext                  |
      | Test assignment name  | student1  | I'm the student1 submission |
      | Test assignment name  | student2  | I'm the student2 submission |
    And I am on the "Test assignment name" Activity page logged in as teacher1
    And I navigate to "Submissions" in current page administration
    When I click on "Actions" "link"
    And "Download grading worksheet" "link" should exist
    And following "Download grading worksheet" should download a file that:
      | Has mimetype  | text/csv                    |
      | Contains text | I'm the student1 submission |
      | Contains text | I'm the student2 submission |
    And following "Download grading worksheet" create a feedback only csv
    And I navigate to "Submissions" in current page administration
    When I click on "Actions" "link"
    And I click on "Upload grading worksheet" "link"
    And I upload "/mod/assign/feedback/offline/tests/fixtures/assignfeedback_offline_grading.csv" file to "Upload a file" filemanager
    And I press "id_submitbutton"
    Then I should see "Confirm changes in grading worksheet"
    And I should see "You did not try very hard."
    And I should see "You put a lot of effort into this."
    And I press "id_submitbutton"
    Then I should see "Updated 0 grades and 2 feedback instances."
    And I press "Continue"
