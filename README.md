Concrete5 ORM
=========================

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