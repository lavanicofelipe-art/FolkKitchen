-- FolksKitchen demo/seed data
-- Run this AFTER folkskitchen_schema.sql has created the tables.
-- Every seeded user shares the password: family123

USE folkskitchen;

INSERT INTO Users (First_Name, Last_Name, Username, Email, Password_Hash, Birthdate, Gender, City, Role, Created_At) VALUES
('Odysseus', 'Odyssey', 'odysseus',   'odysseus@folkskitchen.family', '$2y$10$9LFfeQVJzkLxLPHEhvYJ4.m44tIQm2uEGSLg7bEnkTxwMUlGf7B1.', '1972-03-14', 'Male',              'Athens',    'Admin',       NOW() - INTERVAL 200 DAY),
('Maria',    'Odyssey', 'maria_o',    'maria@folkskitchen.family',    '$2y$10$9LFfeQVJzkLxLPHEhvYJ4.m44tIQm2uEGSLg7bEnkTxwMUlGf7B1.', '1975-07-22', 'Female',            'Athens',    'Admin',       NOW() - INTERVAL 180 DAY),
('Elena',    'Vasquez', 'elena_v',    'elena@folkskitchen.family',    '$2y$10$9LFfeQVJzkLxLPHEhvYJ4.m44tIQm2uEGSLg7bEnkTxwMUlGf7B1.', '1998-11-02', 'Female',            'Barcelona', 'Participant', NOW() - INTERVAL 120 DAY),
('Marcus',   'Odyssey', 'marcus_o',   'marcus@folkskitchen.family',   '$2y$10$9LFfeQVJzkLxLPHEhvYJ4.m44tIQm2uEGSLg7bEnkTxwMUlGf7B1.', '2001-01-30', 'Male',              'Chicago',   'Participant', NOW() - INTERVAL 90 DAY),
('Sophia',   'Reyes',   'sophia_r',   'sophia@folkskitchen.family',   '$2y$10$9LFfeQVJzkLxLPHEhvYJ4.m44tIQm2uEGSLg7bEnkTxwMUlGf7B1.', '1994-05-18', 'Non-binary',        'Manila',    'Participant', NOW() - INTERVAL 60 DAY);

INSERT INTO Categories (Category_Name, Description) VALUES
('Mains',            'Hearty main courses for family dinners'),
('Desserts',         'Sweets, pastries, and holiday treats'),
('Appetizers',       'Starters and small bites'),
('Soups',            'Warm soups and stews'),
('Breads',           'Homemade breads and doughs'),
('Holiday Specials', 'Reserved for feasts and celebrations'),
('Beverages',        'Drinks, teas, and toasts');

-- Recipe 1: Yiayia's Spanakopita (Appetizers, by Odysseus)
INSERT INTO Recipes (Title, Description, Category_ID, Created_By, Prep_Time, Cook_Time, Servings, Created_At)
VALUES ('Yiayia''s Spanakopita', 'Flaky phyllo pie layered with spinach and feta, folded the way grandma taught us.',
  (SELECT Category_ID FROM Categories WHERE Category_Name = 'Appetizers'),
  (SELECT User_ID FROM Users WHERE Email = 'odysseus@folkskitchen.family'),
  35, 45, 8, NOW() - INTERVAL 2 DAY);
SET @r1 = LAST_INSERT_ID();

INSERT INTO Ingredients (Recipe_ID, Ingredient_Name, Quantity, Unit) VALUES
(@r1, 'Phyllo dough sheets', 1, 'lb'),
(@r1, 'Fresh spinach', 2, 'lb'),
(@r1, 'Feta cheese, crumbled', 300, 'g'),
(@r1, 'Eggs', 3, ''),
(@r1, 'Olive oil', 0.5, 'cup'),
(@r1, 'Yellow onion, diced', 1, '');

INSERT INTO Recipe_Steps (Recipe_ID, Step_Number, Instruction) VALUES
(@r1, 1, 'Wilt the spinach with olive oil and diced onion, then let cool completely.'),
(@r1, 2, 'Mix the spinach with crumbled feta and beaten eggs to make the filling.'),
(@r1, 3, 'Layer half the phyllo sheets in a buttered pan, brushing each with oil.'),
(@r1, 4, 'Spread the filling evenly, then layer the remaining phyllo on top, brushing each sheet.'),
(@r1, 5, 'Score the top layer into squares and bake at 375F (190C) until golden, about 45 minutes.');

-- Recipe 2: Sunday Slow-Simmered Ragu (Mains, by Odysseus)
INSERT INTO Recipes (Title, Description, Category_ID, Created_By, Prep_Time, Cook_Time, Servings, Created_At)
VALUES ('Sunday Slow-Simmered Ragu', 'The sauce that means Sunday is coming, simmered for hours with the whole family in and out of the kitchen.',
  (SELECT Category_ID FROM Categories WHERE Category_Name = 'Mains'),
  (SELECT User_ID FROM Users WHERE Email = 'odysseus@folkskitchen.family'),
  20, 240, 10, NOW() - INTERVAL 14 DAY);
SET @r2 = LAST_INSERT_ID();

INSERT INTO Ingredients (Recipe_ID, Ingredient_Name, Quantity, Unit) VALUES
(@r2, 'Ground beef and pork mix', 2, 'lb'),
(@r2, 'San Marzano tomatoes, crushed', 56, 'oz'),
(@r2, 'Garlic cloves, minced', 6, ''),
(@r2, 'Red wine', 1, 'cup'),
(@r2, 'Fresh basil', 0.25, 'cup'),
(@r2, 'Parmesan rind', 1, '');

INSERT INTO Recipe_Steps (Recipe_ID, Step_Number, Instruction) VALUES
(@r2, 1, 'Brown the meat in a heavy pot, then remove and set aside.'),
(@r2, 2, 'Saute garlic until fragrant, deglaze with red wine, and reduce by half.'),
(@r2, 3, 'Add crushed tomatoes, the browned meat, and the parmesan rind.'),
(@r2, 4, 'Simmer uncovered on low heat for at least 4 hours, stirring occasionally.'),
(@r2, 5, 'Stir in fresh basil at the end and season to taste before serving over pasta.');

-- Recipe 3: Grandpa Theo's Baklava (Holiday Specials, by Maria)
INSERT INTO Recipes (Title, Description, Category_ID, Created_By, Prep_Time, Cook_Time, Servings, Created_At)
VALUES ('Grandpa Theo''s Baklava', 'Layers of honeyed phyllo and walnuts, made only when the whole family is together.',
  (SELECT Category_ID FROM Categories WHERE Category_Name = 'Holiday Specials'),
  (SELECT User_ID FROM Users WHERE Email = 'maria@folkskitchen.family'),
  40, 50, 24, NOW() - INTERVAL 5 DAY);
SET @r3 = LAST_INSERT_ID();

INSERT INTO Ingredients (Recipe_ID, Ingredient_Name, Quantity, Unit) VALUES
(@r3, 'Phyllo dough sheets', 1, 'lb'),
(@r3, 'Walnuts, finely chopped', 3, 'cup'),
(@r3, 'Unsalted butter, melted', 1, 'cup'),
(@r3, 'Honey', 1, 'cup'),
(@r3, 'Ground cinnamon', 1, 'tsp'),
(@r3, 'Sugar', 1, 'cup');

INSERT INTO Recipe_Steps (Recipe_ID, Step_Number, Instruction) VALUES
(@r3, 1, 'Mix chopped walnuts with cinnamon and set aside.'),
(@r3, 2, 'Layer buttered phyllo sheets in a baking dish, sprinkling walnut mix between layers.'),
(@r3, 3, 'Score into diamonds before baking at 350F (175C) until golden and crisp.'),
(@r3, 4, 'Simmer honey and sugar into a syrup while the baklava bakes.'),
(@r3, 5, 'Pour the warm syrup over the hot baklava and let it soak for several hours before serving.');

-- Recipe 4: Grandma Ruth's Skillet Cornbread (Breads, by Maria)
INSERT INTO Recipes (Title, Description, Category_ID, Created_By, Prep_Time, Cook_Time, Servings, Created_At)
VALUES ('Grandma Ruth''s Skillet Cornbread', 'Slightly sweet, crisp-edged cornbread baked in the family''s cast iron skillet.',
  (SELECT Category_ID FROM Categories WHERE Category_Name = 'Breads'),
  (SELECT User_ID FROM Users WHERE Email = 'maria@folkskitchen.family'),
  10, 25, 8, NOW() - INTERVAL 30 DAY);
SET @r4 = LAST_INSERT_ID();

INSERT INTO Ingredients (Recipe_ID, Ingredient_Name, Quantity, Unit) VALUES
(@r4, 'Cornmeal', 1.5, 'cup'),
(@r4, 'All-purpose flour', 0.5, 'cup'),
(@r4, 'Buttermilk', 1, 'cup'),
(@r4, 'Eggs', 2, ''),
(@r4, 'Honey', 3, 'tbsp'),
(@r4, 'Butter', 4, 'tbsp');

INSERT INTO Recipe_Steps (Recipe_ID, Step_Number, Instruction) VALUES
(@r4, 1, 'Preheat the cast iron skillet in the oven at 400F (200C) with the butter inside.'),
(@r4, 2, 'Whisk together cornmeal, flour, buttermilk, eggs, and honey.'),
(@r4, 3, 'Carefully pour the hot melted butter into the batter and stir quickly.'),
(@r4, 4, 'Pour the batter into the hot skillet and bake for about 22-25 minutes until golden.');

-- Recipe 5: Nonna's Christmas Eve Fish Soup (Soups, by Odysseus)
INSERT INTO Recipes (Title, Description, Category_ID, Created_By, Prep_Time, Cook_Time, Servings, Created_At)
VALUES ('Nonna''s Christmas Eve Fish Soup', 'A brothy, garlicky fish soup served every Christmas Eve without fail.',
  (SELECT Category_ID FROM Categories WHERE Category_Name = 'Soups'),
  (SELECT User_ID FROM Users WHERE Email = 'odysseus@folkskitchen.family'),
  25, 35, 6, NOW() - INTERVAL 1 DAY);
SET @r5 = LAST_INSERT_ID();

INSERT INTO Ingredients (Recipe_ID, Ingredient_Name, Quantity, Unit) VALUES
(@r5, 'White fish fillets', 1.5, 'lb'),
(@r5, 'Shrimp', 1, 'lb'),
(@r5, 'Fish stock', 6, 'cup'),
(@r5, 'Fennel bulb, sliced', 1, ''),
(@r5, 'Crushed tomatoes', 2, 'cup'),
(@r5, 'Garlic cloves', 4, '');

INSERT INTO Recipe_Steps (Recipe_ID, Step_Number, Instruction) VALUES
(@r5, 1, 'Saute fennel and garlic in olive oil until softened.'),
(@r5, 2, 'Add crushed tomatoes and fish stock, then bring to a gentle simmer.'),
(@r5, 3, 'Simmer for 15 minutes to let the broth develop flavor.'),
(@r5, 4, 'Add the fish and shrimp and poach gently until just cooked through, about 8 minutes.'),
(@r5, 5, 'Ladle into bowls and serve with crusty bread.');

-- Bookmarks so the archive feels lived-in
INSERT INTO Bookmarks (User_ID, Recipe_ID, Date_Bookmarked) VALUES
((SELECT User_ID FROM Users WHERE Email = 'elena@folkskitchen.family'), @r1, NOW() - INTERVAL 1 DAY),
((SELECT User_ID FROM Users WHERE Email = 'elena@folkskitchen.family'), @r3, NOW() - INTERVAL 3 DAY),
((SELECT User_ID FROM Users WHERE Email = 'marcus@folkskitchen.family'), @r2, NOW() - INTERVAL 5 DAY),
((SELECT User_ID FROM Users WHERE Email = 'sophia@folkskitchen.family'), @r5, NOW() - INTERVAL 1 DAY);

-- Activity log history
INSERT INTO Activity_Logs (User_ID, Recipe_ID, Action, Log_Date) VALUES
((SELECT User_ID FROM Users WHERE Email = 'odysseus@folkskitchen.family'), @r5, 'Created recipe', NOW() - INTERVAL 1 DAY),
((SELECT User_ID FROM Users WHERE Email = 'odysseus@folkskitchen.family'), @r1, 'Updated recipe', NOW() - INTERVAL 2 DAY),
((SELECT User_ID FROM Users WHERE Email = 'maria@folkskitchen.family'),    @r3, 'Created recipe', NOW() - INTERVAL 5 DAY),
((SELECT User_ID FROM Users WHERE Email = 'elena@folkskitchen.family'),    @r1, 'Bookmarked recipe', NOW() - INTERVAL 1 DAY),
((SELECT User_ID FROM Users WHERE Email = 'marcus@folkskitchen.family'),   @r2, 'Bookmarked recipe', NOW() - INTERVAL 5 DAY),
((SELECT User_ID FROM Users WHERE Email = 'maria@folkskitchen.family'),    @r4, 'Created recipe', NOW() - INTERVAL 30 DAY);
