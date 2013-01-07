Staq
======
Staq is a small PHP framework for a enjoyable web development.



Features
--------


### Stack
The main feature is that you can instantiate a stack of classes through all your extensions with <code>new \Stack\Model\User</code>,
where other frameworks limits you to one specific extension with <code>new \My\Extension\Prefixes\Model\User</code>.

1. **Reduce weight**: you have a light syntax with more meaning ;
2. **Reduce dependency**: you can start to use a stack even before defining it ;
3. **Increase reusability**: you can add an external extension to complete your own stack.


### Router
Soon! It's under development.


### Model 
Planned for the version v0.5.


### Extendable applications
Planned for the version v0.7.



Getting Started
--------


### System Requirements
You need **PHP >= 5.4** and some happiness.


### Hello world tutorial 

```php
require_once( 'path/to/Staq/include.php' );

\Staq\application( 'Hello_World' )
    ->add_controller( '/*', function( ) {
        return 'Hello World';
    } )
    ->run( );
```

License
--------
Staq is under [MIT License](http://opensource.org/licenses/MIT)



Roadmap
--------
The last stable version is [v0.2](https://github.com/Pixel418/Staq/tree/v0.2).

I am working hard on the [v0.3](https://github.com/Pixel418/Staq/tree/v0.3). <br>
If you are curious on the next features, you can see my [trello board](https://trello.com/board/staq/50de3fe18942735c620000a9).
