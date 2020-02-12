# 规则解析工厂
使用抽象工厂方法实现，调用RuleClient解析预定义的规则，获得数据
## 一、扩展
1、需继承抽象实体类 AbsRules 实现 decodeRule 方法
2、需实现工厂接口 ITRulesFactory
3、修改 RuleClient 添加扩展的工厂
## 二、使用
### 1、时间解析工厂（TimeRule）
 规则： time|-1 month|Y-m-d H:i:s
 使用：time是关键词，第一个管道做运算，第二个管道格式化
 1、返回当前时间戳： time
 2、返回当前时间并格式化成Y-m-d： time||Y-m-d
 3、返回当前时间减去一天的时间戳： time|-1 day