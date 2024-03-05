# Compass

**Effortless Routing for PHP Applications**

Used in [robertkleinschuster/zenith](https://github.com/robertkleinschuster/zenith)

Compass is a routing solution for PHP, inspired by the simplicity and power of NEXT.js routing. It's engineered to enhance PHP applications by allowing developers to define routes through a straightforward directory and file structure, offering an intuitive approach to building dynamic web applications.

## Elevating PHP Routing

Compass transforms traditional PHP routing, providing a structured yet flexible way to map URLs to your application's content. By using simple `page.php`, `layout.php` and `action.php` files within a designated directory structure, developers can easily align URL paths with specific PHP files, streamlining the routing process and enhancing maintainability.

## Quickstart

### Installation

Initiate your journey with Compass by integrating it into your PHP project through Composer:

```bash
composer require robertkleinschuster/compass
```

### Crafting Your Routes

Organize your application's endpoints using a `routes` directory. Place `page.php` files to denote your routes and `layout.php` for layouts. These files should return a value that is renderable by the [robertkleinschuster/mosaic](https://github.com/robertkleinschuster/mosaic) renderer.

#### Structuring Your Directory

```
/routes
    /home
        page.php        # Maps to /home
    /about
        page.php        # Maps to /about
    /products
        page.php        # Maps to /products
        layout.php      # Layout for product overview and details view
        /details
            page.php    # Maps to /products/details
    /layout.php         # Shared layout for your routes
```

### Defining Content

Each `page.php`, `layout.php`, or `action.php` file must return a value that is renderable by the mosaic renderer. This ensures seamless integration and consistency across your application's user interface.

### Parameters for Pages, Layouts, and Actions

When a route is accessed, the corresponding `page.php`, `layout.php`, or `action.php` file is invoked with specific parameters:

- **For `page.php`**: The function receives `array $params` for route parameters and `array $queryParams` for query string values. It is also passed the current `Route $route` object.
- **For `layout.php`**: In addition to all parameters of page.php this function is passed `mixed $children`, which represents the content nested within the layout. An important distinction for the `Route $route` object passed to layouts is that it is not the currently matched but rather the object of the level the layout.php is placed in.
- **For `action.php`**: The function receives `array $params` and `array $queryParams`, similar to `page.php`, to handle specific actions based on the request. Additionally, it receives `array $parsedBody`.

### License

Compass is distributed under the MIT License. See LICENSE for more information.
