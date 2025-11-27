# 📦 Recommended Packages

Enhance your NeoFramework applications with these carefully curated third-party packages. All packages are tested, maintained, and widely used in the community.

## Table of Contents

- [Development Tools](#development-tools)
- [Debugging and Profiling](#debugging-and-profiling)
- [Testing Utilities](#testing-utilities)
- [Database Extensions](#database-extensions)
- [API Development](#api-development)
- [Authentication and Security](#authentication-and-security)
- [File Management](#file-management)
- [Email and Notifications](#email-and-notifications)
- [Caching](#caching)
- [Admin Panels](#admin-panels)
- [UI Components](#ui-components)
- [Utilities](#utilities)

## 🛠️ Development Tools

### Code Quality

**PHP CS Fixer**
```bash
composer require --dev friendsofphp/php-cs-fixer
```
Automatically fix PHP coding standards issues.

Configuration:
```php
// .php-cs-fixer.php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/app', __DIR__ . '/src']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
    ])
    ->setFinder($finder);
```

Usage:
```bash
vendor/bin/php-cs-fixer fix
```

**PHPStan**
```bash
composer require --dev phpstan/phpstan
```
Static analysis tool for finding bugs.

Configuration:
```yaml
# phpstan.neon
parameters:
    level: 8
    paths:
        - app
        - src
    excludePaths:
        - vendor
```

Usage:
```bash
vendor/bin/phpstan analyse
```

**Psalm**
```bash
composer require --dev vimeo/psalm
```
Alternative static analysis tool with advanced type checking.

```bash
vendor/bin/psalm --init
vendor/bin/psalm
```

### Code Generation

**Neo Generators** (Built-in)
```bash
# Generate controller
neo make:controller UserController

# Generate model
neo make:model User --migration

# Generate middleware
neo make:middleware AuthMiddleware

# Generate service provider
neo make:provider CacheServiceProvider
```

**Faker**
```bash
composer require fakerphp/faker
```
Generate fake data for testing and seeding.

```php
use Faker\Factory;

$faker = Factory::create();

$user = [
    'name' => $faker->name,
    'email' => $faker->email,
    'address' => $faker->address,
    'phone' => $faker->phoneNumber,
];
```

## 🐛 Debugging and Profiling

### Whoops

```bash
composer require filp/whoops
```
Beautiful error page for development.

Configuration:
```php
// bootstrap/app.php
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

if (config('app.debug')) {
    $whoops = new Run;
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();
}
```

### Symfony VarDumper

```bash
composer require symfony/var-dumper
```
Advanced debugging tool.

```php
// Instead of var_dump()
dump($variable);

// Die and dump
dd($variable);

// Dump to log
dump($variable)->log();
```

### Clockwork

```bash
composer require itsgoingd/clockwork
```
Browser-based debugging tool.

Features:
- Request/response inspection
- Database queries
- Cache operations
- Performance metrics
- Timeline view

Access: `http://your-app.local/__clockwork`

### Ray

```bash
composer require spatie/ray
```
Desktop debugging tool by Spatie.

```php
ray('Debug message');
ray($variable)->blue();
ray($array)->label('User data');

// Conditional debugging
ray()->if(condition(), 'Condition is true');

// Measure execution time
ray()->measure(fn() => slowOperation());
```

## 🧪 Testing Utilities

### Mockery

```bash
composer require --dev mockery/mockery
```
Powerful mocking framework.

```php
use Mockery as m;

class UserServiceTest extends TestCase
{
    public function test_creates_user()
    {
        $repository = m::mock(UserRepository::class);
        $repository->shouldReceive('create')
            ->once()
            ->with(['name' => 'John'])
            ->andReturn(new User(['name' => 'John']));
        
        $service = new UserService($repository);
        $user = $service->createUser(['name' => 'John']);
        
        $this->assertEquals('John', $user->name);
    }
}
```

### PHPUnit Extensions

**Prophecy** (Alternative to Mockery)
```bash
composer require --dev phpspec/prophecy-phpunit
```

```php
public function test_creates_user()
{
    $repository = $this->prophesize(UserRepository::class);
    $repository->create(['name' => 'John'])
        ->willReturn(new User(['name' => 'John']));
    
    $service = new UserService($repository->reveal());
    $user = $service->createUser(['name' => 'John']);
    
    $this->assertEquals('John', $user->name);
}
```

### Database Testing

**Database Transactions Trait**
```php
use Neo\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;
    
    public function test_user_can_be_created()
    {
        $user = User::create(['name' => 'John']);
        $this->assertDatabaseHas('users', ['name' => 'John']);
        
        // Automatically rolled back after test
    }
}
```

**Factory Bot**
```bash
composer require --dev league/factory-muffin
```
Advanced factory pattern for tests.

### HTTP Testing

**Guzzle**
```bash
composer require guzzlehttp/guzzle
```
HTTP client for testing APIs.

```php
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://api.example.com']);
$response = $client->get('/users');

$this->assertEquals(200, $response->getStatusCode());
$users = json_decode($response->getBody());
```

## 💾 Database Extensions

### Doctrine DBAL

```bash
composer require doctrine/dbal
```
Powerful database abstraction layer.

```php
use Doctrine\DBAL\DriverManager;

$connection = DriverManager::getConnection([
    'dbname' => 'mydb',
    'user' => 'root',
    'password' => 'secret',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
]);

$users = $connection->fetchAllAssociative('SELECT * FROM users');
```

### MongoDB Integration

```bash
composer require mongodb/mongodb
```
MongoDB PHP library.

```php
use MongoDB\Client;

$client = new Client('mongodb://localhost:27017');
$collection = $client->mydb->users;

$collection->insertOne([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

$users = $collection->find(['active' => true]);
```

### Redis Integration

```bash
composer require predis/predis
```
Redis client for PHP.

```php
use Predis\Client;

$redis = new Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

$redis->set('key', 'value');
$value = $redis->get('key');

// Pub/Sub
$redis->publish('channel', 'message');
```

### Database Seeders

**Nelmio Alice**
```bash
composer require --dev nelmio/alice
```
Fixtures generator.

```yaml
# fixtures.yaml
Neo\Models\User:
    user_{1..10}:
        name: '<name()>'
        email: '<email()>'
        password: '<password()>'
```

## 🌐 API Development

### Fractal

```bash
composer require league/fractal
```
API transformer for consistent output.

```php
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'links' => [
                'self' => route('users.show', $user->id),
            ],
        ];
    }
}

$manager = new Manager();
$resource = new Collection($users, new UserTransformer());
$data = $manager->createData($resource)->toArray();
```

### API Documentation

**Swagger/OpenAPI**
```bash
composer require zircote/swagger-php
```
Generate API documentation.

```php
/**
 * @OA\Get(
 *     path="/api/users",
 *     summary="Get list of users",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation"
 *     )
 * )
 */
public function index()
{
    return User::all();
}
```

**API Doc Generator**
```bash
composer require --dev mpociot/laravel-apidoc-generator
```
Automatically generate API documentation.

### JSON API

```bash
composer require neomerx/json-api
```
JSON API specification implementation.

```php
use Neomerx\JsonApi\Encoder\Encoder;

$encoder = Encoder::instance([
    User::class => UserSchema::class,
]);

$json = $encoder->encodeData($users);
```

### GraphQL

```bash
composer require webonyx/graphql-php
```
GraphQL implementation for PHP.

```php
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'user' => [
            'type' => $userType,
            'args' => [
                'id' => ['type' => Type::int()],
            ],
            'resolve' => fn($root, $args) => User::find($args['id']),
        ],
    ],
]);

$schema = new Schema(['query' => $queryType]);
```

## 🔐 Authentication and Security

### JWT Authentication

```bash
composer require firebase/php-jwt
```
JSON Web Token implementation.

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Generate token
$payload = [
    'iss' => 'your-app.com',
    'sub' => $user->id,
    'iat' => time(),
    'exp' => time() + 3600,
];

$token = JWT::encode($payload, $secret, 'HS256');

// Verify token
$decoded = JWT::decode($token, new Key($secret, 'HS256'));
```

### OAuth2 Server

```bash
composer require league/oauth2-server
```
OAuth2 server implementation.

```php
use League\OAuth2\Server\AuthorizationServer;

$server = new AuthorizationServer(
    $clientRepository,
    $accessTokenRepository,
    $scopeRepository,
    $privateKey,
    $encryptionKey
);
```

### Two-Factor Authentication

```bash
composer require pragmarx/google2fa
```
Google Authenticator 2FA.

```php
use PragmaRx\Google2FA\Google2FA;

$google2fa = new Google2FA();

// Generate secret
$secret = $google2fa->generateSecretKey();

// Generate QR code URL
$qrCodeUrl = $google2fa->getQRCodeUrl(
    'YourApp',
    $user->email,
    $secret
);

// Verify code
$valid = $google2fa->verifyKey($secret, $code);
```

### Security Headers

```bash
composer require bepsvpt/secure-headers
```
Set security headers easily.

```php
use Bepsvpt\SecureHeaders\SecureHeaders;

$headers = new SecureHeaders();
$response = $headers->send($response);
```

## 📁 File Management

### Flysystem

```bash
composer require league/flysystem
```
Filesystem abstraction.

```php
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

$adapter = new LocalFilesystemAdapter('/path/to/root');
$filesystem = new Filesystem($adapter);

$filesystem->write('file.txt', 'Contents');
$contents = $filesystem->read('file.txt');
$filesystem->delete('file.txt');
```

**AWS S3 Support**:
```bash
composer require league/flysystem-aws-s3-v3
```

### Image Processing

**Intervention Image**
```bash
composer require intervention/image
```

```php
use Intervention\Image\ImageManagerStatic as Image;

// Resize image
$img = Image::make('photo.jpg')->resize(300, 200);
$img->save('thumbnail.jpg');

// Add watermark
$img->insert('watermark.png', 'bottom-right', 10, 10);

// Apply filters
$img->greyscale()->blur();
```

### PDF Generation

**DomPDF**
```bash
composer require dompdf/dompdf
```

```php
use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml('<h1>Hello World</h1>');
$dompdf->render();
$dompdf->stream('document.pdf');
```

**TCPDF**
```bash
composer require tecnickcom/tcpdf
```
Alternative PDF library with more features.

### Excel/CSV

**PhpSpreadsheet**
```bash
composer require phpoffice/phpspreadsheet
```

```php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Hello World!');

$writer = new Xlsx($spreadsheet);
$writer->save('hello.xlsx');
```

## 📧 Email and Notifications

### SwiftMailer

```bash
composer require swiftmailer/swiftmailer
```
Email library (if not using built-in mailer).

### Email Validation

```bash
composer require egulias/email-validator
```

```php
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

$validator = new EmailValidator();
$validator->isValid('email@example.com', new RFCValidation()); // true
```

### SMS Notifications

**Twilio**
```bash
composer require twilio/sdk
```

```php
use Twilio\Rest\Client;

$client = new Client($accountSid, $authToken);

$client->messages->create(
    '+1234567890',
    [
        'from' => '+0987654321',
        'body' => 'Hello from NeoFramework!'
    ]
);
```

### Push Notifications

**OneSignal**
```bash
composer require onesignal/onesignal-php-api
```

```php
use OneSignal\OneSignal;

$oneSignal = new OneSignal($appId, $apiKey);
$oneSignal->sendNotification([
    'contents' => ['en' => 'Hello World'],
    'included_segments' => ['All']
]);
```

## 🗄️ Caching

### APCu

```bash
# Enable in php.ini
extension=apcu.so
apc.enabled=1
```

```php
apcu_store('key', 'value', 3600);
$value = apcu_fetch('key');
```

### Memcached Client

```bash
composer require memcached
```

```php
$memcached = new Memcached();
$memcached->addServer('127.0.0.1', 11211);
$memcached->set('key', 'value', 3600);
```

### Redis

Already covered in Database Extensions section.

## 🎨 Admin Panels

### Custom Admin

**NeoAdmin** (Fictional, but example structure)
```bash
composer require neo/admin
```

Features:
- CRUD generation
- User management
- Role-based access
- Dashboard widgets
- File manager

### Backpack

```bash
composer require backpack/crud
```
Popular admin panel generator.

## 🎨 UI Components

### Frontend Assets

**Laravel Mix** (Compatible)
```bash
npm install laravel-mix --save-dev
```

```javascript
// webpack.mix.js
let mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');
```

### UI Frameworks Integration

**Bootstrap**
```bash
npm install bootstrap
```

**Tailwind CSS**
```bash
npm install tailwindcss
```

**Alpine.js**
```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

## 🔧 Utilities

### Carbon

```bash
composer require nesbot/carbon
```
DateTime manipulation.

```php
use Carbon\Carbon;

$now = Carbon::now();
$yesterday = Carbon::yesterday();
$tomorrow = Carbon::tomorrow();

$date = Carbon::parse('2024-01-01');
$date->addDays(7);
$date->format('Y-m-d'); // 2024-01-08

$diff = $now->diffInDays($date);
```

### Collection

```bash
composer require illuminate/collections
```
Powerful array manipulation.

```php
use Illuminate\Support\Collection;

$collection = collect([1, 2, 3, 4, 5]);

$filtered = $collection->filter(fn($item) => $item > 2);
$mapped = $collection->map(fn($item) => $item * 2);
$sum = $collection->sum();
```

### Str and Arr Helpers

```bash
composer require illuminate/support
```

```php
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

// String helpers
Str::slug('Hello World'); // hello-world
Str::camel('hello_world'); // helloWorld
Str::random(16); // random string

// Array helpers
Arr::get($array, 'key.nested', 'default');
Arr::has($array, 'key');
Arr::flatten($array);
```

### UUID Generation

```bash
composer require ramsey/uuid
```

```php
use Ramsey\Uuid\Uuid;

$uuid = Uuid::uuid4();
echo $uuid->toString(); // e.g., 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
```

### URL Manipulation

```bash
composer require league/uri
```

```php
use League\Uri\Uri;

$uri = Uri::createFromString('http://example.com/path?query=value');
echo $uri->getHost(); // example.com
echo $uri->getPath(); // /path
```

### HTML/XML Parsing

**Symfony DomCrawler**
```bash
composer require symfony/dom-crawler
composer require symfony/css-selector
```

```php
use Symfony\Component\DomCrawler\Crawler;

$html = '<html><body><h1>Hello</h1></body></html>';
$crawler = new Crawler($html);

$text = $crawler->filter('h1')->text(); // Hello
```

### Markdown Parser

```bash
composer require league/commonmark
```

```php
use League\CommonMark\CommonMarkConverter;

$converter = new CommonMarkConverter();
$html = $converter->convert('# Hello World')->getContent();
```

---

These packages will significantly enhance your NeoFramework development experience. Choose the ones that fit your project needs! 🚀
