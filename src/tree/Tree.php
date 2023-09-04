<?php

namespace tools\tree;

/**
 * 通用的树型类
 * @author XiaoYao <476552238li@gmail.com>
 */
class Tree
{
    protected static $instance;
    //默认配置
    protected array $config = [];
    public array $options = [];

    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $arr = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public array $icon = array('│', '├', '└');
    public string $nbsp = "&nbsp;";
    public string $pidName = 'pid';

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Tree
     */
    public static function instance(array $options = []): Tree
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 初始化方法
     * @param array $arr     2维数组，例如：
     *      array(
     *      1 => array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','pid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','pid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','pid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
     *      )
     * @param string|null $pidName 父字段名称
     * @param string|null $nbsp    空格占位符
     * @return Tree
     */
    public function init(array $arr = [], string $pidName = null, string $nbsp = null): Tree
    {
        $this->arr = $arr;
        if (!is_null($pidName)) {
            $this->pidName = $pidName;
        }
        if (!is_null($nbsp)) {
            $this->nbsp = $nbsp;
        }
        return $this;
    }

    /**
     * 得到子级数组
     * @param int
     * @return array
     */
    public function getChild($myId): array
    {
        $newArr = [];
        foreach ($this->arr as $value) {
            if (!isset($value['id'])) {
                continue;
            }
            if ($value[$this->pidName] == $myId) {
                $newArr[$value['id']] = $value;
            }
        }
        return $newArr;
    }

    /**
     * 读取指定节点的所有孩子节点
     * @param int $myId     节点ID
     * @param boolean $withSelf 是否包含自身
     * @return array
     */
    public function getChildren(int $myId, bool $withSelf = false): array
    {
        $newArr = [];
        foreach ($this->arr as $value) {
            if (!isset($value['id'])) {
                continue;
            }
            if ((string)$value[$this->pidName] == (string)$myId) {
                $newArr[] = $value;
                $newArr = array_merge($newArr, $this->getChildren($value['id']));
            } elseif ($withSelf && (string)$value['id'] == (string)$myId) {
                $newArr[] = $value;
            }
        }
        return $newArr;
    }

    /**
     * 读取指定节点的所有孩子节点ID
     * @param int $myId     节点ID
     * @param boolean $withSelf 是否包含自身
     * @return array
     */
    public function getChildrenIds(int $myId, bool $withSelf = false): array
    {
        $childrenList = $this->getChildren($myId, $withSelf);
        $childrenIds = [];
        foreach ($childrenList as $v) {
            $childrenIds[] = $v['id'];
        }
        return $childrenIds;
    }

    /**
     * 得到当前位置父辈数组
     * @param int
     * @return array
     */
    public function getParent($myId): array
    {
        $pid = 0;
        $newArr = [];
        foreach ($this->arr as $value) {
            if (!isset($value['id'])) {
                continue;
            }
            if ($value['id'] == $myId) {
                $pid = $value[$this->pidName];
                break;
            }
        }
        if ($pid) {
            foreach ($this->arr as $value) {
                if ($value['id'] == $pid) {
                    $newArr[] = $value;
                    break;
                }
            }
        }
        return $newArr;
    }

    /**
     * 得到当前位置所有父辈数组
     * @param int $myId
     * @param bool $withSelf 是否包含自己
     * @return array
     */
    public function getParents(int $myId, bool $withSelf = false): array
    {
        $pid = 0;
        $newArr = [];
        foreach ($this->arr as $value) {
            if (!isset($value['id'])) {
                continue;
            }
            if ($value['id'] == $myId) {
                if ($withSelf) {
                    $newArr[] = $value;
                }
                $pid = $value[$this->pidName];
                break;
            }
        }
        if ($pid) {
            $arr = $this->getParents($pid, true);
            $newArr = array_merge($arr, $newArr);
        }
        return $newArr;
    }

    /**
     * 读取指定节点所有父类节点ID
     * @param int $myId
     * @param boolean $withSelf
     * @return array
     */
    public function getParentsIds(int $myId, bool $withSelf = false): array
    {
        $parentList = $this->getParents($myId, $withSelf);
        $parentsIds = [];
        foreach ($parentList as $k => $v) {
            $parentsIds[] = $v['id'];
        }
        return $parentsIds;
    }

    /**
     * 树型结构Option
     * @param int $myId        表示获得这个ID下的所有子级
     * @param string $itemTpl     条目模板 如："<option value=@id @selected @disabled>@spacer@name</option>"
     * @param mixed  $selectedIds 被选中的ID，比如在做树型下拉框的时候需要用到
     * @param mixed  $disabledIds 被禁用的ID，比如在做树型下拉框的时候需要用到
     * @param string $itemPrefix  每一项前缀
     * @param string $topTpl      顶级栏目的模板
     * @return string
     */
    public function getTree(int $myId, string $itemTpl = "<option value=@id @selected @disabled>@spacer@name</option>", $selectedIds = '', $disabledIds = '', string $itemPrefix = '', string $topTpl = ''): string
    {
        $ret = '';
        $number = 1;
        $children = $this->getChild($myId);
        if ($children) {
            $total = count($children);
            foreach ($children as $value) {
                $id = $value['id'];
                $j = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                    $k = $itemPrefix ? $this->nbsp : '';
                } else {
                    $j .= $this->icon[1];
                    $k = $itemPrefix ? $this->icon[0] : '';
                }
                $spacer = $itemPrefix ? $itemPrefix . $j : '';
                $selected = $selectedIds && in_array($id, (is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds))) ? 'selected' : '';
                $disabled = $disabledIds && in_array($id, (is_array($disabledIds) ? $disabledIds : explode(',', $disabledIds))) ? 'disabled' : '';
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled, 'spacer' => $spacer));
                $value = array_combine(array_map(function ($k) {
                    return '@' . $k;
                }, array_keys($value)), $value);
                $nStr = strtr((($value["@{$this->pidName}"] == 0 || $this->getChild($id)) && $topTpl ? $topTpl : $itemTpl), $value);
                $ret .= $nStr;
                $ret .= $this->getTree($id, $itemTpl, $selectedIds, $disabledIds, $itemPrefix . $k . $this->nbsp, $topTpl);
                $number++;
            }
        }
        return $ret;
    }

    /**
     * 树型结构UL
     * @param int $myId        表示获得这个ID下的所有子级
     * @param string $itemTpl     条目模板 如："<li value=@id @selected @disabled>@name @childlist</li>"
     * @param string $selectedIds 选中的ID
     * @param string $disabledIds 禁用的ID
     * @param string $wrapTag     子列表包裹标签
     * @param string $wrapAttr    子列表包裹属性
     * @return string
     */
    public function getTreeUl(int $myId, string $itemTpl, string $selectedIds = '', string $disabledIds = '', string $wrapTag = 'ul', string $wrapAttr = ''): string
    {
        $str = '';
        $children = $this->getChild($myId);
        if ($children) {
            foreach ($children as $value) {
                $id = $value['id'];
                unset($value['child']);
                $selected = $selectedIds && in_array($id, (is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds))) ? 'selected' : '';
                $disabled = $disabledIds && in_array($id, (is_array($disabledIds) ? $disabledIds : explode(',', $disabledIds))) ? 'disabled' : '';
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));
                $value = array_combine(array_map(function ($k) {
                    return '@' . $k;
                }, array_keys($value)), $value);
                $nStr = strtr($itemTpl, $value);
                $childData = $this->getTreeUl($id, $itemTpl, $selectedIds, $disabledIds, $wrapTag, $wrapAttr);
                $childList = $childData ? "<{$wrapTag} {$wrapAttr}>" . $childData . "</{$wrapTag}>" : "";
                $str .= strtr($nStr, array('@childlist' => $childList));
            }
        }
        return $str;
    }

    /**
     * 菜单数据
     * @param int $myId
     * @param string $itemTpl
     * @param mixed  $selectedIds
     * @param mixed  $disabledIds
     * @param string $wrapTag
     * @param string $wrapAttr
     * @param int $deepLevel
     * @return string
     */
    public function getTreeMenu(int $myId, string $itemTpl, $selectedIds = '', $disabledIds = '', string $wrapTag = 'ul', string $wrapAttr = '', int $deepLevel = 0): string
    {
        $str = '';
        $children = $this->getChild($myId);
        if ($children) {
            foreach ($children as $value) {
                $id = $value['id'];
                unset($value['child']);
                $selected = in_array($id, (is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds))) ? 'selected' : '';
                $disabled = in_array($id, (is_array($disabledIds) ? $disabledIds : explode(',', $disabledIds))) ? 'disabled' : '';
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));
                $value = array_combine(array_map(function ($k) {
                    return '@' . $k;
                }, array_keys($value)), $value);
                $bakValue = array_intersect_key($value, array_flip(['@url', '@caret', '@class']));
                $value = array_diff_key($value, $bakValue);
                $nStr = strtr($itemTpl, $value);
                $value = array_merge($value, $bakValue);
                $childData = $this->getTreeMenu($id, $itemTpl, $selectedIds, $disabledIds, $wrapTag, $wrapAttr, $deepLevel + 1);
                $childList = $childData ? "<{$wrapTag} {$wrapAttr}>" . $childData . "</{$wrapTag}>" : "";
                $childList = strtr($childList, array('@class' => $childData ? 'last' : ''));
                $value = array(
                    '@childlist' => $childList,
                    '@url'       => $childData || !isset($value['@url']) ? "javascript:;" : $value['@url'],
                    '@addtabs'   => $childData || !isset($value['@url']) ? "" : (stripos($value['@url'], "?") !== false ? "&" : "?") . "ref=addtabs",
                    '@caret'     => ($childData && (!isset($value['@badge']) || !$value['@badge']) ? '<i class="fa fa-angle-left"></i>' : ''),
                    '@badge'     => $value['@badge'] ?? '',
                    '@class'     => ($selected ? ' active' : '') . ($disabled ? ' disabled' : '') . ($childData ? ' treeview' . (config('fastadmin.show_submenu') ? ' treeview-open' : '') : ''),
                );
                $str .= strtr($nStr, $value);
            }
        }
        return $str;
    }

    /**
     * 特殊
     * @param integer $myId        要查询的ID
     * @param string $itemTpl1    第一种HTML代码方式
     * @param string $itemTpl2    第二种HTML代码方式
     * @param mixed   $selectedIds 默认选中
     * @param mixed   $disabledIds 禁用
     * @param string $itemPrefix  前缀
     * @return string
     */
    public function getTreeSpecial(int $myId, string $itemTpl1, string $itemTpl2, $selectedIds = 0, $disabledIds = 0, string $itemPrefix = ''): string
    {
        $ret = '';
        $number = 1;
        $children = $this->getChild($myId);
        if ($children) {
            $total = count($children);
            foreach ($children as $id => $value) {
                $j = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                    $k = $itemPrefix ? $this->nbsp : '';
                } else {
                    $j .= $this->icon[1];
                    $k = $itemPrefix ? $this->icon[0] : '';
                }
                $spacer = $itemPrefix ? $itemPrefix . $j : '';
                $selected = $selectedIds && in_array($id, (is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds))) ? 'selected' : '';
                $disabled = $disabledIds && in_array($id, (is_array($disabledIds) ? $disabledIds : explode(',', $disabledIds))) ? 'disabled' : '';
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled, 'spacer' => $spacer));
                $value = array_combine(array_map(function ($k) {
                    return '@' . $k;
                }, array_keys($value)), $value);
                $nStr = strtr(!isset($value['@disabled']) || !$value['@disabled'] ? $itemTpl1 : $itemTpl2, $value);

                $ret .= $nStr;
                $ret .= $this->getTreeSpecial($id, $itemTpl1, $itemTpl2, $selectedIds, $disabledIds, $itemPrefix . $k . $this->nbsp);
                $number++;
            }
        }
        return $ret;
    }

    /**
     *
     * 获取树状数组
     * @param string $myId       要查询的ID
     * @param string $itemPrefix 前缀
     * @return array
     */
    public function getTreeArray(string $myId, string $itemPrefix = ''): array
    {
        $children = $this->getChild($myId);
        $n = 0;
        $data = [];
        $number = 1;
        if ($children) {
            $total = count($children);
            foreach ($children as $id => $value) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                    $k = $itemPrefix ? $this->nbsp : '';
                } else {
                    $j .= $this->icon[1];
                    $k = $itemPrefix ? $this->icon[0] : '';
                }
                $spacer = $itemPrefix ? $itemPrefix . $j : '';
                $value['spacer'] = $spacer;
                $data[$n] = $value;
                $data[$n]['childlist'] = $this->getTreeArray($id, $itemPrefix . $k . $this->nbsp);
                $n++;
                $number++;
            }
        }
        return $data;
    }

    /**
     * 将getTreeArray的结果返回为二维数组
     * @param array $data
     * @param string $field
     * @return array
     */
    public function getTreeList(array $data = [], string $field = 'name'): array
    {
        $arr = [];
        foreach ($data as $k => $v) {
            $childList = $v['childlist'] ?? [];
            unset($v['childlist']);
            $v[$field] = $v['spacer'] . ' ' . $v[$field];
            $v['haschild'] = $childList ? 1 : 0;
            if ($v['id']) {
                $arr[] = $v;
            }
            if ($childList) {
                $arr = array_merge($arr, $this->getTreeList($childList, $field));
            }
        }
        return $arr;
    }
}
