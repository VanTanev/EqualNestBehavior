EqualNestBehavior
================

[![Build Status](https://secure.travis-ci.org/CraftyShadow/EqualNestBehavior.png?branch=master)](http://travis-ci.org/CraftyShadow/EqualNestBehavior)

The EqualNestBehavior is inspired by [Doctrine's Equal Nest Relations](http://www.doctrine-project.org/documentation/manual/1_0/en/defining-models:relationships:join-table-associations:self-referencing-nest-relations:equal-nest-relations)
implementation, and provides a way to define relations between objects that have
equal hierarchy - think about a person and his friends.


### Requirements

This behavior requires Propel >= 1.6.0.


### Installation

Get the code by cloning this repository, or by using Composer (recommended):

```javascript
{
    "require": {
        "craftyshadow/propel-equalnest-behavior": "dev-master"
    }
}
```

Then, if you don't use Composer, or an autoloader in your application, add the
following configuration to your `build.properties` or `propel.ini` file:

```ini
propel.behavior.equal_nest.class = vendor.craftyshadow.propel-equalnest-behavior.src.EqualNestBehavior
```

> Note: `vendor.craftyshadow.propel-equalnest-behavior.src.EqualNestBehavior` is the path to access the `EqualNestBehavior` class in "dot-path" notation.


Then declare the behavior in your `schema.xml`:

```xml
<table name="person">
  <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
  <column name="name" type="VARCHAR" required="true" />
</table>

<table name="friend">
  <behavior name="equal_nest">
    <parameter name="parent_table" value="person" />
  </behavior>
  <!-- you do not need to specify any colums for the "friend" table, the behavior will add them automatically -->
</table>
```


#### Setup with symfony 1.4

Copy the behavior to `lib/vendor/equal_nest_behavior` and then register the
behavior class by adding the following to the bottom of the `config/propel.ini`
file:

```ini
; check that you have behaviors enabled
propel.builder.addBehaviors = true

; and add the custom behavior
propel.behavior.equal_nest.class = lib.vendor.equal_nest_behavior.EqualNestBehavior
```

Then in your `schema.yml`:

```yaml
propel:

  person:
    id: ~
    name: { type: varchar(255) }

  friend:
    _propel_behaviors:
      symfony: { form: false, filter: false }
      equal_nest:
        parent_table: person
    # you do not need to specify any columns for the "friend" table, the behavior will add them automatically
```


### Usage

Continuing with the example above, here is how you would use the `Person` object
to define `Friend` relationships:

```php
<?php

$john  = new Person();
$peter = new Person();
$marry = new Person();

$john->addFriend($peter);
$john->addFriend($marry);
$john->addFriends(array($peter, $marry)); // same as the above

$john->getFriends(); // returns a PropelObjectCollection ($peter, $marry)

$john->hasFriend($peter); // true
$peter->hasFriend($john); // true

$john->removeFriends();
$john->getFriends(); // empty array
$john->save(); // commit to the DB
```

The most important thing to remember is that **all changes are committed to the
database only after you call the `->save()` method!**

Also, keep in mind that **relations are non-transitional**:

```php
<?php
$john->addFriend($marry);
$marry->addFriend($peter);
$john->hasFriend($peter); // false
```

If you need this you will have to manually implement it.


### ActiveRecord API

```php
<?php

$person->addFriend($friend);

$person->hasFriend($friend);

// get all friends, will be cached if no citeria specified. Filtered by the criteria otherwize
$person->getFriends($criteria = null, $con = null);

// replace the current collection of friends
$person->setFriends($friends_array);

// append to the current collection of friends
$person->addFriends($friends_array);

// remove a specfic friend
$person->removeFriend($friend)

// remove all friends
$person->removeFriends();

$person->countFriends($criteria = null, $distinct = false, $con = null);
```


### ActiveRecord API

```php
<?php

PersonQuery::create()->findFriendsOf($person, $con = null);

PersonQuery::create()->countFriendsOf($person, $con = null);
```


### Parameters

```xml
<behavior name="equal_nest">
    <parameter name="parent_table"       value="person" />
    <parameter name="reference_column_1" value="friend_1" />
    <parameter name="reference_column_1" value="friend_2" />
</behavior>
```


### Propel pluralizer

This behavior makes use of the Propel pluralizer when generating the relational
interface. Make sure to always use the `StandardEnglishPluralizer`
[bundled with Propel 1.6](http://propel.posterous.com/propel-gets-better-at-naming-things)
and up whenever possible (eg when you are starting a new project).

To use the better pluralizer add the following to your `build.properties` (for
standalone Propel) or `propel.ini` (for symfony + Propel):

```ini
propel.builder.pluralizer.class = builder.util.StandardEnglishPluralizer
```


### Running tests

Install dependencies:

    php composer.phar install --dev

Run the test suite:

    phpunit


### License

This behavior is released under the MIT License. See the bundled LICENSE file for details.
