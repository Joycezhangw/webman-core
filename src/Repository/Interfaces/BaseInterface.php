<?php
declare (strict_types=1);

namespace Landao\WebmanCore\Repository\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Closure;

interface BaseInterface
{
    /**
     * 获取当前的model对象
     * @return Model
     */
    public function getModel(): Model;


    /**
     * 根据主键查询
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * 按字段值查询单条数据
     * @param $field
     * @param null $value
     * @param array $columns
     * @return mixed
     */
    public function findByField($field, $value = null, $columns = ['*']);

    /**
     * 按字段值查询列表
     * @param $field
     * @param null $value
     * @param array $columns
     * @return mixed
     */
    public function findAllByField($field, $value = null, $columns = ['*']);

    /**
     * 根据查询条件获取数据
     * @param array $where
     * @param array $columns
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*']);

    /**
     * 根据字段多个值获取数据列表
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereIn(string $field, array $values, $columns = ['*']);

    /**
     * 查询不在指定字段值中的数据
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereNotIn(string $field, array $values, $columns = ['*']);

    /**
     * 判断数据是否存在，存在返回true 不存在返回false
     * @param array $where 查询条件
     * @return bool
     */
    public function existsWhere(array $where): bool;

    /**
     * 判断数据是否存在，存在返回 false 不存在返回 true
     * @param array $where
     * @return bool
     */
    public function doesntExistWhere(array $where): bool;

    /**
     * 根据主键id获取单条数据
     * @param int $id 主键id
     * @return mixed
     */
    public function getInfoById(int $id);

    /**
     * 根据条件，获取一条指定字段数据
     * @param array $columns 查询字段
     * @return mixed
     */
    public function first(array $columns = ['*']);

    /**
     * 查找第一条数据，获取创建
     * @param array $attributes
     * @return mixed
     */
    public function firstOrCreate(array $attributes = []);

    /**
     * 没有查找到数据，抛出异常
     * @param array $condition
     * @return mixed
     */
    public function firstOrFail(array $condition);

    /**
     * 根据条件，获取全部数据
     * @param array $columns 要查询的字段
     * @return mixed
     */
    public function all(array $columns = ['*']);


    /**
     * 联动查询条件，
     * @param $where
     * @return $this
     */
    public function where($where);

    /**
     * 设置查询数量
     * @param int $limit
     * @return $this|mixed
     */
    public function limit(int $limit);

    /**
     * 设置要查询的字段
     * @param array ...$values
     * @return mixed
     */
    public function select($values);

    /**
     * 排序
     * @param $column
     * @param string $direction
     * @return mixed
     */
    public function orderBy($column, $direction = 'asc');

    /**
     * 获取分页
     * @param array $columns
     * @param int $limit
     * @return mixed
     */
    public function paginate($columns = ['*'], $limit = 0);


    /**
     * 创建一条数据，不联表状态
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * 批量插入
     * @param array $attributes
     * @return mixed
     */
    public function createBatch(array $attributes);


    /**
     * 根据主键id，更新一条数据
     * @param array $attributes 要更新的字段
     * @param int $id 更新主键值
     * @return mixed
     */
    public function updateById(array $attributes, int $id);

    /**
     * 根据指定条件更新数据，批量更新
     * @param array $condition 更新条件
     * @param array $attributes 要更新的字段
     * @return mixed
     */
    public function updateByWhere(array $condition, array $attributes);


    /**
     * 删除单条数据
     * @param int $id 主键值
     * @return int
     */
    public function deleteById(int $id);

    /**
     * 根据id批量删除
     * @param array $ids
     * @return mixed
     */
    public function deleteByIds(array $ids);

    /**
     * 根据主键，更新某个字段，模型要指定主键名
     * @param int $id 主键id值
     * @param string $filedName 字段名称
     * @param string $fieldValue 字段值
     * @return mixed
     */
    public function updateFieldById(int $id, string $filedName, string $fieldValue);

    /**
     * 统计数量
     *
     * 注意：
     *     1.不建议使用 $columns='*'，请指定特定字段名，如果没指定，默认为主键字段名
     *     2.不建议用 count() 来判断数据存不存在，请使用find 或者 first 来判断数据是否存在
     *
     * @param array $condition 查询条件
     * @param string $columns 统计字段
     * @return int
     */
    public function count(array $condition = [], string $columns = ''): int;

    /**
     * 求和
     * @param array $condition
     * @param string $columns
     * @return mixed
     */
    public function sum(array $condition = [], string $columns = '');

    /**
     * 求平均值
     * @param array $condition
     * @param string $columns
     * @return mixed
     */
    public function avg(array $condition = [], string $columns = '');

    /**
     * 求最大值
     * @param array $condition
     * @param string $columns
     * @return mixed
     */
    public function max(array $condition = [], string $columns = '');

    /**
     * 求最小值
     * @param array $condition
     * @param string $columns
     * @return mixed
     */
    public function min(array $condition = [], string $columns = '');

    /**
     * 指定某个字段值自增
     * @param array $condition
     * @param string $filedName
     * @param int $amount
     * @return mixed
     */
    public function increment(array $condition, string $filedName, int $amount = 1);

    /**
     * 指定某个字段递减
     * @param array $condition
     * @param string $filedName
     * @param int $amount
     * @return mixed
     */
    public function decrement(array $condition, string $filedName, int $amount = 1);

    /**
     * 解析一条业务数据
     * @param array $row
     * @return array
     */
    public function parseDataRow(array $row): array;

    /**
     * 解析多条业务数据格式，循环调用 parseDataRow 方法，只需要在具体的业务逻辑继承重写 parseDataRow 方法即可
     * @param array $rows
     * @return array
     */
    public function parseDataRows(array $rows): array;

    /**
     * 得到某个列的数组
     * 使用场景：以 key 为数组索引，获取指定字段。或者直接获取一个字段得一维数组，比如：[1001,1002,1003] (只获取uid) 字段
     * @param string $column 字段名 多个字段用逗号分隔
     * @param array $condition 查询条件
     * @param string $key 指定索引名
     * @return array
     */
    public function column(string $column, $condition = [], string $key = ''): array;

    /**
     * 批量更新多条数据，默认更新主键为id，若不是，就以数组第一个主键为key进行更熟数据
     * @param array $multipleData 二维数据
     * @return bool
     */
    public function updateBatch(array $multipleData = []): bool;


    /**
     * 更新或者创建
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * 根据条件批量删除数据
     * @param array $condition
     * @return mixed
     */
    public function deleteWhere(array $condition);

    /**
     * @param array $relations
     * @return $this
     */
    public function with(array $relations);

    /**
     * 关联模型计数
     * @param array $relations
     * @return mixed
     */
    public function withCount(array $relations);

    /**
     * 同步关联
     * @param $id
     * @param $relation
     * @param $attributes
     * @param bool $detaching
     * @return mixed
     */
    public function sync($id, $relation, $attributes, $detaching = true);

    /**
     * 执行事务
     * 传入匿名函数就是自动，不传入就是手动
     * @param callable|null $callable
     * @return mixed
     */
    public function transaction(callable $callable = null);

    /**
     * 事务回滚
     * @return mixed
     */
    public function rollBack();

    /**
     * 提交事务
     * @return mixed
     */
    public function commit();

}