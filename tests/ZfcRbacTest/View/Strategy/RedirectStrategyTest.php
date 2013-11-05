<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfcRbacTest\View\Helper;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\SimpleRouteStack;
use ZfcRbac\Options\RedirectStrategyOptions;
use ZfcRbac\View\Strategy\RedirectStrategy;

/**
 * @covers \ZfcRbac\View\Strategy\RedirectStrategy
 */
class RedirectStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCanRedirectWithoutPreviousUri()
    {
        $response = new HttpResponse();

        $router = new SimpleRouteStack();
        $router->addRoute('login', array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/login'
            )
        ));

        $mvcEvent = new MvcEvent();
        $mvcEvent->setResponse($response);
        $mvcEvent->setError('error');
        $mvcEvent->setRouter($router);

        $options = new RedirectStrategyOptions(array(
            'redirect_to_route'   => 'login',
            'append_previous_uri' => false
        ));

        $redirectStrategy = new RedirectStrategy($options);

        $redirectStrategy->onError($mvcEvent);

        $this->assertNotSame($response, $mvcEvent->getResponse(), 'Assert a new response is created');
        $this->assertEquals(302, $mvcEvent->getResponse()->getStatusCode());
        $this->assertEquals('/login', $mvcEvent->getResponse()->getHeaders()->get('Location')->getFieldValue());
    }

    public function testCanRedirectWithPreviousUri()
    {
        $response = new HttpResponse();

        $router = new SimpleRouteStack();
        $router->addRoute('login', array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/login'
            )
        ));

        $request = new HttpRequest();
        $request->setUri('http://www.example.com/previous');

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest($request);
        $mvcEvent->setResponse($response);
        $mvcEvent->setError('error');
        $mvcEvent->setRouter($router);

        $options = new RedirectStrategyOptions(array(
            'redirect_to_route'      => 'login',
            'append_previous_uri'    => true,
            'previous_uri_query_key' => 'redirectTo'
        ));

        $redirectStrategy = new RedirectStrategy($options);

        $redirectStrategy->onError($mvcEvent);

        $this->assertNotSame($response, $mvcEvent->getResponse(), 'Assert a new response is created');
        $this->assertEquals(302, $mvcEvent->getResponse()->getStatusCode());
        
        $this->assertEquals(
            '/login?redirectTo=http://www.example.com/previous',
            $mvcEvent->getResponse()->getHeaders()->get('Location')->getFieldValue()
        );
    }
}
 