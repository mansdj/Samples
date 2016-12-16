Application Scope

Develop an application that meets the following feature requirements:

- Create a web page in PHP that uses the two MySQL tables above “Colors” and “Votes”.
- The left column should be populated from reading all the entries in the Colors table.
- The colors should be links, so that when you click on it, an Ajax call populates the Votes (obtained from MySQL) in the right column next to the color.
- When Clicking on “Total”, use JavaScript only (no server involvement) to add up and present the totals shown.

Development Thought Process

Application Architecture

The application uses a N-Tier architecture.  The "web" directory contains the single "view", the index page.  The directory also contains the appropriate CSS, JS, and Fonts folders for 
front-end presentation.  The "app" directory contains the classes and models necessary for supporting the application on the back-end, with a bootstrapper class(Init.php) to handle the
path definitions and autoloading.  This approach allowed for quicker development without the need of a router since this application uses only one "view".  Yet, a router could be 
implemented without major code or architecture overhaul.

Security

There are implementations to avoid SQL injections.  The first primary method is the use of prepared statements when variables are being passed to the application.  Secondly, the
Colors model has a method to ensure that the color parameter provided is in the colors table.  This also permits the ability to scale application by adding new colors without code changes.
These methods help maintain the integrity of the data being passed to the database without fear of compromise.

Models

The models somewhat implement an ORM philosophy by mirroring the class properties with the database table fields.  The models are also where the database processing takes place.

Library

For this application a database connection class is the only class located in the library directory.

Front-End

The Bootstrap and jQuery libraries were implement to speed up the front-end development process.  A modular JS pattern was implemented for handling the votes to further separate 
business logic from the presentation logic.