# Custom Form Actions

A custom action is usually defined by extending the \Neos\Fusion\Form\Runtime\Action\AbstractAction with a custom class
and implementing the `perform` method. Inside of perform the defined options are available via `$this->options`.

```
namespace Vendor\Site\Action

use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Flow\Mvc\ActionResponse;

class MessageAction extends AbstractAction
{
    /**
     * @return ActionResponse|null
     */
    public function perform(): ?ActionResponse
    {
        $response = new ActionResponse();
        $response->setContent($this->options['message']);
        return $response;
    }
}
```

The action class can afterwards be used in a form-action:

```
    action {
        message {
            type = 'Vendor.Site:Message'
            options.message = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
        }
    }
```    

The type identifier is resolved to a classname like `[namespace]\\Action\\[name]`. If the created action uses 
a different namespace you can use the full classname as `type` as well.  
