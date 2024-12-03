<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;


use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\AbstractResult;

class Forward extends AbstractResult
{

    private string|null $module = null;
    private string|null $controller = null;
    private array $params = [];

    public function __construct(
        private readonly Request $request
    )
    {
    }

    /**
     * @param string $module
     * @return $this
     */
    public function setModule(string $module): self
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @param string $controller
     * @return $this
     */
    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @param string $action
     * @return $this
     */
    public function forward(string $action): self
    {
        // This only collects the initial request before forwarding
        // $this->request->initForward();

        if (!empty($this->params)) {
            $this->request->setParams($this->params);
        }

        if (!empty($this->controller)) {
            //  $this->request->setControllerName($this->controller);

            // Module should only be reset if controller has been specified
            if (!empty($this->module)) {
                //     $this->request->setModuleName($this->module);
            }
        }

//        $this->request->setActionName($action);
        $this->request->setMatched(false);
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    protected function render(HttpResponseInterface $response): self
    {
        return $this;
    }

}
