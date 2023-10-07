# Megio starter
Skeleton for your apps, webs & APIs built on Megio.

- Docs: https://megio.dev
- Installation: https://megio.dev/docs/getting-started/installation

## Template rendering
```php
class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->render(Path::viewDir() . '/controller/home.latte', [
            'title' => 'Megio Panel',
            'description' => 'Most powerful tool for creating webs, apps & APIs.'
        ]);
    }
}
```

## API request handling
```php
class DownloadRequest extends Request
{
    // Inject custom dependencies here
    public function __construct(protected readonly EntityManager $em) 
    {
    }
    
    // Add request validations
    public function schema(): array
    {
        return [
            'invoice' => Expect::structure([
                'type' => Expect::anyOf(
                    'invoice', 'proforma', 'invoice-annul', 'proforma-annul'
                ),
                'number' => Expect::string()->required(),
                'issue_date' => Expect::string()->required(),
                'due_date' => Expect::string()->required(),
                'currency' => Expect::string()->required(),
                'items_text' => Expect::string(),
                'footer_text' => Expect::string(),
            ])->required()->castTo('array')
        ];
    }
    
    // Process validated data
    public function process(array $data): Response
    {
        $invoice = new Invoice();
        $invoice->setType($data['invoice']['type'])
        
        ...
        
        $this->em->persist($invoice)
        $this->em->flush()
        
        return $this->json(['id' => $invoice->getId()]);
    }
}
```

## Routing
```php
return static function (RoutingConfigurator $routes): void {
    $routes->add('download', '/api/invoice/download')
        ->methods(['POST'])
        ->controller(\App\Http\Request\Invoice\DownloadRequest::class)
        ->options(['auth' => false]);
    
    $routes->add('home', '/')
        ->methods(['GET'])
        ->controller([\App\Http\Controller\HomeController::class, 'index'])
        ->options(['auth' => false]);
};
```
