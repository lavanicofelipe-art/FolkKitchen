CREATE TABLE Users
(
  User_ID       INT          NOT NULL AUTO_INCREMENT,
  First_Name    VARCHAR(50)  NOT NULL,
  Last_Name     VARCHAR(50)  NOT NULL,
  Username      VARCHAR(50)  NOT NULL,
  Email         VARCHAR(100) NOT NULL,
  Password_Hash VARCHAR(255) NOT NULL,
  Birthdate     DATE         NOT NULL,
  Gender        VARCHAR(20)  NOT NULL,
  City          VARCHAR(100) NOT NULL,
  Role          VARCHAR(20)  NOT NULL,
  Created_At    DATETIME     NOT NULL,
  PRIMARY KEY (User_ID)
);

CREATE TABLE Categories
(
  Category_ID   INT         NOT NULL AUTO_INCREMENT,
  Category_Name VARCHAR(50) NOT NULL,
  Description   TEXT        NOT NULL,
  PRIMARY KEY (Category_ID)
);

CREATE TABLE Recipes
(
  Recipe_ID   INT          NOT NULL AUTO_INCREMENT,
  Title       VARCHAR(100) NOT NULL,
  Description TEXT         NOT NULL,
  Category_ID INT          NOT NULL,
  Created_By  INT          NOT NULL,
  Prep_Time   INT          NOT NULL,
  Cook_Time   INT          NOT NULL,
  Servings    INT          NOT NULL,
  Created_At  DATETIME     NOT NULL,
  PRIMARY KEY (Recipe_ID)
);

CREATE TABLE Ingredients
(
  Ingredient_ID   INT           NOT NULL AUTO_INCREMENT,
  Recipe_ID       INT           NOT NULL,
  Ingredient_Name VARCHAR(100)  NOT NULL,
  Quantity        DECIMAL(8,2)  NOT NULL,
  Unit            VARCHAR(30)   NOT NULL,
  PRIMARY KEY (Ingredient_ID)
);

CREATE TABLE Recipe_Steps
(
  Step_ID     INT     NOT NULL AUTO_INCREMENT,
  Recipe_ID   INT     NOT NULL,
  Step_Number INT     NOT NULL,
  Instruction TEXT    NOT NULL,
  PRIMARY KEY (Step_ID)
);

CREATE TABLE Bookmarks
(
  Bookmark_ID     INT      NOT NULL AUTO_INCREMENT,
  User_ID         INT      NOT NULL,
  Recipe_ID       INT      NOT NULL,
  Date_Bookmarked DATETIME NOT NULL,
  PRIMARY KEY (Bookmark_ID)
);

CREATE TABLE Activity_Logs
(
  Log_ID    INT         NOT NULL AUTO_INCREMENT,
  User_ID   INT         NOT NULL,
  Recipe_ID INT         NOT NULL,
  Action    VARCHAR(50) NOT NULL,
  Log_Date  DATETIME    NOT NULL,
  PRIMARY KEY (Log_ID)
);

ALTER TABLE Users
  ADD CONSTRAINT UQ_email UNIQUE (Email);

ALTER TABLE Users
  ADD CONSTRAINT UQ_username UNIQUE (Username);

ALTER TABLE Recipes
  ADD CONSTRAINT FK_Categories_TO_Recipes
    FOREIGN KEY (Category_ID)
    REFERENCES Categories (Category_ID);

ALTER TABLE Recipes
  ADD CONSTRAINT FK_Users_TO_Recipes
    FOREIGN KEY (Created_By)
    REFERENCES Users (User_ID);

ALTER TABLE Ingredients
  ADD CONSTRAINT FK_Recipes_TO_Ingredients
    FOREIGN KEY (Recipe_ID)
    REFERENCES Recipes (Recipe_ID);

ALTER TABLE Recipe_Steps
  ADD CONSTRAINT FK_Recipes_TO_Recipe_Steps
    FOREIGN KEY (Recipe_ID)
    REFERENCES Recipes (Recipe_ID);

ALTER TABLE Bookmarks
  ADD CONSTRAINT FK_Users_TO_Bookmarks
    FOREIGN KEY (User_ID)
    REFERENCES Users (User_ID);

ALTER TABLE Bookmarks
  ADD CONSTRAINT FK_Recipes_TO_Bookmarks
    FOREIGN KEY (Recipe_ID)
    REFERENCES Recipes (Recipe_ID);

ALTER TABLE Activity_Logs
  ADD CONSTRAINT FK_Users_TO_Activity_Logs
    FOREIGN KEY (User_ID)
    REFERENCES Users (User_ID);

ALTER TABLE Activity_Logs
  ADD CONSTRAINT FK_Recipes_TO_Activity_Logs
    FOREIGN KEY (Recipe_ID)
    REFERENCES Recipes (Recipe_ID);
