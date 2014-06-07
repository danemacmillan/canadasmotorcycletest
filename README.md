# Canada's Motorcycle Test Cart

The purpose of this test is to demonstrate coding ability.

## How to install

You don't really need to install this. A working demo is available at
https://hack.danemacmillan.com/canadasmotorcycletest. However, should you
really want to install this, all you need to do is clone this repo, expose
the `app` directory to the public on your server, and create a database in
MySQL/Percona/MariaDB called `canadas_motorcycle`. When you first visit the
`index.php` page, the dispatcher will take care of setting up the database
tables--one for `products` and one for `cart`--and populating them with
test data.

## Requirements

- PHP 5.4+ (due to lambdas, closures, anonymous functions, namespaces)

- A MySQL-based database

- a Web server (Apache or Nginx)

## A bit about the code

- It works with *and* without JavaScript. The cart will gracefully degrade, and
is also progressively enhanced for users with JavaScript. This means
quantities will always be adjusted, regardless of available technology.

- For reasons of scope, this test demo is as fully-featured as it requires.
True production-ready code would demand a slightly more thorough validation
process. Nevertheless, this codebase is very robust.

- The backend is all natural. Meaning, there are no frameworks or CMS' in use
to bloat this small test. This makes installing and running quite easy. The
code is designed to mirror closely most typical MVC frameworks, but obviously
less meaty.

- Two database tables are created. They are effectively denormalized, to
reduce redundancy. These tables are created once, then setup files are created,
which prevent the setup from repeating.

- The frontend makes use of a custom font, `Open Sans`, the `Font Awesome`
icon set, and the lastest version of `jQuery` (2.1.1). This version of jQuery
deliberately dropped support for IE6/7/8. See note below.

- Custom JavaScript was written in `strict` mode, and `promises` were used for
handling asynchronous requests.

- The side panel was designed to follow the user on screen, so that the prices
are always in view. This is a great UX pattern for shopping carts.

- The UI was designed to be very fresh feeling, spacious, and generally easy to
look at. In addition, the markup is written very semantically, so that users
with screen readers can still make sense of it. This is also very important
for SEO purposes. The DOCTYPE in use is HTML5, which means it can be very
loose with validation; regardless, I've written it to be like valid XHTML5,
which is a less common hybrid of XHTML 1.1 strict and HTML5. The CSS makes
use of transitions and other version 3 eye candy.

- This was not tested in IE, and for the purposes of this test demo, I could
care less about it. Nevertheless, it should work just fine down to IE6
(with some expected eyesores), due to the way the cart was designed. IE9+
should be just fine. Every other popular browser was successfully tested
(Firefox, Chrome, Safari, Opera).

- There is a little Easter egg in the checkout process.

---

# About Dane MacMillan

He's a pretty fly guy. "Motorcycles are super cool," is something he says
constantly.
