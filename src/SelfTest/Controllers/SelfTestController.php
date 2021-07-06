<?php

namespace Jsantoso\LaravelServices\SelfTest\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Exception;

class SelfTestController extends Controller {

    public function getTestData() {
        $tests = [];
        
        $enabledPluginClasses = config('selftest.plugins');
        foreach ($enabledPluginClasses as $pluginClass) {
                        
            $pluginObj = new $pluginClass();
            if (is_a($pluginObj, SelfTestPluginInterface::class)) {
                
                $actions = [];
                foreach ($pluginObj->getTestActions() as $testAction) {
                    
                    $action = new stdClass();
                    $action->id = $testAction->getId();
                    $action->label = $testAction->getActionLabel();
                    $action->plugin = $pluginClass;
                    $action->name = $testAction->getActionName();
                    
                    $actions[] = $action;
                    
                }
                
                $test = new stdClass();
                $test->name = $pluginObj->getTestName();
                $test->actions = $actions;
                
                $tests[] = $test;
            }
        }
        
        return view('selftest::selftest', [
            'tests'    => $tests,
            'testData' => json_encode($tests)
        ]);
    }
    
    public function getResult(Request $request) {

        $pluginClass = $request->input('plugin');
        $name = $request->input('name');
        
        $output = array(
            'success' => false
        );
        
        try {
            
            if (class_exists($pluginClass)) {
                $pluginObj = new $pluginClass();
                if (is_a($pluginObj, SelfTestPluginInterface::class)) {
                    
                    foreach ($pluginObj->getTestActions() as $actionObj) {
                        if ($name == $actionObj->getActionName()) {
                            $output['success'] = $actionObj->runActionTest();
                        }
                    }
                    
                }
            }
        } catch (Exception $ex) {
            return response($ex->getMessage(), 400)->header('Content-Type', 'text/plain');
        }
        return response()->json($output);
    }

}
