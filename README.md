Concrete5 ORM
=========================

C5orm is an object-relational mapping (ORM) for concrete5 cms. C5orm is inspired by eloquent, so most of c5orm syntaq is pretty similar with eloquent syntaq.

## Usage Instructions
> `composer require "fudyartanto/c5orm"`

```PHP
use Fudyartanto\C5orm\Model;

class MyTable extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected static $table = 'MyTableName';
}

// Retrieve a model by its primary key
$mytable = MyTable::find(1)
```

## Documentation

The documentation is included in this repo in the root directory, and publicly available at https://fudyartanto.github.io/c5orm/. The documentation may also be run locally.