# Equal Nest Behavior

The Equal Nest Behavior is inspired by [Doctrine's Equal Nest Relations](http://www.doctrine-project.org/documentation/manual/1_0/en/defining-models:relationships:join-table-associations:self-referencing-nest-relations:equal-nest-relations) implementation and provides a way to define relations between objects that have equal hierarchy - think about a person and his friends.

## Setup with vanilla Propel

Copy the behavior to `generator/lib/behavior/equal_nest` and then register the behavior class by adding the following to the bottom of the `build.properties` file in you project folder:

```ini
# add custom behavior
propel.behavior.equal_nest.class = behavior.equal_nest.EqualNestBehavior
```

Then in your schema.xml:

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

## Setup with symfony 1.4

Copy the behavior to `lib/vendor/equal_nest_behavior` and then register the behavior class by adding the following to the bottom of the `config/propel.ini` file:

```ini
propel.behavior.equal_nest.class = lib.vendor.equal_nest_behavior.EqualNestBehavior
```

Then in your schema.yml:

```yaml
propel:

  person:
    id: ~
    name: { type: varchar(255) }

  friend:
    _propel_behaviors:
      equal_nest:
        parent_table: person
    # you do not need to specify any columns for the "friend" table, the behavior will add them automatically
```

## Usage

Continuing with the example above, here is how you would use the Person object to define Friend relationships:

```php
<?php
$john = new Person();
$peter = new Person();
$marry = new Person();

$john->addFriend($peter);
$john->addFriend($marry);

$john->getFriends(); // returns a PropelObjectCollection ($peter, $marry)

$john->addFriends(array($peter, $marry)); // same as the above

$john->hasFriend($peter); // true
$peter->hasFriend($john); // true

$john->removeFriends();
$john->getFriends(); // empty array
$john->save(); // commit to the DB
```

The most important thing to remember is that **all changes are committed to the database only after you call the `->save()` method!**

### Keep in mind that relations are non-transitional:

```php
<?php
$john->addFriend($marry);
$marry->addFriend($peter);
$john->hasFriend($peter); // false
```

If you need this you will have to manually implement it.

## Full behavior settings (You do not need to use this, but it's good to know):

```xml
<table name="person">
  <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
  <column name="name" type="VARCHAR" required="true" />
</table>

<table name="friend">
  <behavior name="equal_nest">
    <parameter name="parent_table" value="person" />
    <parameter name="reference_column_1" value="friend_1" />
    <parameter name="reference_column_1" value="friend_2" />
  </behavior>

  <colum name="friend_1" required="true" primaryKey="true" />
  <colum name="friend_2" required="true" primaryKey="true" />

  <foreign-key foreignTable="person" onDelete="cascade">
    <reference local="friend_1" foreign="id" />
  </foreign-key>

  <foreign-key foreignTable="person" onDelete="cascade">
    <reference local="friend_2" foreign="id" />
  </foreign-key>
</table>
```


## Full API:

```php
<?php
$person->addFriend($friend);

$person->hasFriend($friend);

$person->getFriends($criteria = null, $con = null); // get all friends, will be cached if no citeria specified. Filtered by the criteria otherwize

$person->setFriends($friends_array); // replace the current collection of friends

$person->addFriends($friends_array); // append to the current collection of friends

$person->removeFriend($friend) // remove a specfic friend

$person->removeFriends(); // remove all friends

$person->countFriends($criteria = null, $distinct = false, $con = null);


PersonQuery::create()->findFriendsOf($person, $con = null);

PersonQuery::create()->countFriendsOf($person, $con = null);
```


## License

Equal Nest Behavior for Propel is released under the MIT Licence.

Copyright (c) 2011 Ivan Tanev

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.