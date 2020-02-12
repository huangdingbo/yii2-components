# 多进程助手

## 使用

### 回调方式
  ```php
         $params = ['a','b','c','d','e'];
                
         $process = new MultipleProcessesHelper();
         $process->setProcessNum(count($params));
         $process->setCallback(function ()use ($process,$params){
            var_dump($process->getIndex());
            var_dump($params[$process->getIndex()]);
          });
         $process->execute(); 
  ```
### 处理类方式
  ```php
        $params = ['a','b','c','d','e'];

        $process = new MultipleProcessesHelper();
        $process->setProcessNum(count($params));
        $process->setHandler(TestHander::className(),$process,$params);
        $process->execute();
  ```
### 说明
setHandler 第一个参数接收处理类的类名，第二个参数是进程助手类的引用，用于判断是那个进程
params 是传入助手类的参数
  