# PostTypes TTG Fork

This is an updated version of the package https://github.com/jjgrainger/posttype


# Notes
* There are many things that need to be changed in the class, but it is a pretty solid starting point
* The sub classes Columns, & Taxonomy should be switched over to traits
  1. The need to not contain the same variables as the parent class, public $options is a big offender
  2. The references to them need to be made removed and methods called directly from $this

## Post_Date_Handler
This is a set of functions that handles dates on post types.
Currently it is only handling current and past events, using an
'archive field'. Here is how it works:

```php
//Set the archive date to the post meta key 'event_date'
$events->init_dates(['archive' => 'event_date'])
```

You are all set!

#### This will set up two post meta fields:
* _is_upcoming = 1 || 0
* _event_upcoming_label = Upcoming || Past

#### This sets up two actions
* On post save it checks to see if the event is past
* Every day it checks the upcoming events to see if they have past

#### Set up a facet to toggle between the two:
* Use radio buttons (You might be able to use other, I only tested this one.)
* select data source: _event_upcoming_label


##Todos
I have added todo comment tags where I questions or things are wonky

Things might also be wonky in other areas. Feed back is appreciated!