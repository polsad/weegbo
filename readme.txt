0.8.15
    Refactoring base/extension/* classes

0.8.14
    DB static class remade to extansion
    Updated jQuery (version 1.7.2) & HTMLPurfier (version 4.4.0)
    Refactoring base/* classes
    Load time
    Recoding Error & Extension classes
    Add debug levels to config

0.8.13
    Added rowCount method - return number of records for INSERT, UPDATE, DELETE queries
    Added multiple connection for DB

0.8.12
    Recoding Loader class (mechanism for loading helpers) 

0.8.11
    Updated jQuery (version 1.7)
    В Input added $_FILES

0.8.10
    Added Profiler class
    
0.8.9
    Bugfix for $_GET
    
0.8.8
    Deleted input extension, added Input static class
    
0.8.7
    Inspire Framework is done. Hello Weegbo!

0.8.6
    Minnor bugs
    Added APC в cache extension

0.8.5
    Recoding Loader class (methods for loading models, libs and extensions). Now using ReflectionClass

0.8.4
    Recoding session extension (support crossdomain session)
    Added config/autoload.php file - autoload components
    Changed redirect method in base/conltroller.class.php, now support external urls
    Added mrthod for split domain (for orgaization name.example.com)
    Notice fix

0.8.3
    Bugfix for paging, acl, file, validator extensions. Bugfix for base, error, view static classes.

0.8.2
    Added GException exception.
    Recoding Error static class.
    
0.8.1
    Add to input extension method for xss protection
    Add to image extension method for thumb
    Recoding for validator extension
    
0.8
    Added base class
    Recoding method redirect() in controller.class.php - deleted $_SERVER['HTTP_REFERER']

0.7.1
    Recoding errors mechanism (now generated exception)

0.7
    Новый виток (Highload)
    Deleted Smarty and added View static class
    Added static Error class
    Deleted DbSimple and added DB static class