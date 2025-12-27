<?php
namespace App\Helpers;

use App\Models\Services;

/**
 * Class to store the entire group tree
 */
class NodesTree
{
    var $id = 0;
    var $name = '';
    var $parent_id = '';
    var $slug = '';
    var $complimentory = '';
    var $active = '';
    var $duration = '';
    var $price = '';
    var $color = '';
    var $end_node = '';
    var $description = '';
    var $image_src = '';
    var $is_mobile = '';
    var $category_name = ''; // Add category_name property
    var $non_negative_groups = false;

    var $children_groups = array();
    var $children_nodes = array();

    var $counter = 0;

    var $current_id = -1;

    var $default_text = 'Please select...';

    /**
     * Initializer
     */
    function NodesTree()
    {
        return;
    }

    /**
     * Setup which group id to start from
     */
    function build($id, $account_id, $end_node = true, $only_active = false)
    {
        if ($id == 0)
        {
            $this->id = 0;
            $this->name = "None";
            $this->active = 1;
            $this->category_name = null;
        } else {
            $where = [];
            $where['id'] = $id;
            $where['account_id'] = $account_id;

            if($end_node) {
                $where['end_node'] = 0;
            }
            if($only_active) {
                $where['active'] = 1;
            }

            $group = Services::with('category')->where($where)->first();

            if (!$group) {
                return; // Handle case where group is not found
            }

            $this->id = $group->id;
            $this->name = $group->name;
            $this->parent_id = $group->parent_id;
            $this->slug = $group->slug;
            $this->complimentory = $group->complimentory;
            $this->active = $group->active;
            $this->duration = $group->duration;
            $this->price = $group->price;
            $this->color = $group->color;
            $this->end_node = $group->end_node;
            $this->description = $group->description;
            $this->image_src = $group->image_src;
            $this->is_mobile = $group->is_mobile;
            $this->category_name = $group->category ? $group->category->name : null; // Set category name
        }

        $this->add_sub_nodes($account_id, $only_active);
        $this->add_sub_groups($account_id, $only_active);
    }

    /**
     * Find and add subgroups as objects
     */
    function add_sub_groups($account_id, $only_active = false)
    {
        $where = [
            'parent_id' => $this->id,
            'end_node' => 0,
            'account_id' => $account_id
        ];
        if ($only_active) {
            $where['active'] = 1;
        }

        $child_group_q = Services::with('category')->where($where)->orderBy('name', 'asc')->get();

        $counter = 0;
        foreach ($child_group_q as $row)
        {
            $this->children_groups[$counter] = new NodesTree();
            $this->children_groups[$counter]->current_id = $this->current_id;
            $this->children_groups[$counter]->non_negative_groups = $this->non_negative_groups;
            $this->children_groups[$counter]->build($row->id, $account_id);
            $counter++;
        }
    }

    /**
     * Find and add subnodes as array items
     */
    function add_sub_nodes($account_id, $only_active = false)
    {
        $where = [
            'parent_id' => $this->id,
            'end_node' => 1,
            'account_id' => $account_id
        ];
        if ($only_active) {
            $where['active'] = 1;
        }

        $child_node_q = Services::with('category')->where($where)->orderBy('name', 'asc')->get();

        $counter = 0;
        foreach ($child_node_q as $row)
        {
            $this->children_nodes[$counter]['id'] = $row->id;
            $this->children_nodes[$counter]['name'] = $row->name;
            $this->children_nodes[$counter]['parent_id'] = $row->parent_id;
            $this->children_nodes[$counter]['slug'] = $row->slug;
            $this->children_nodes[$counter]['complimentory'] = $row->complimentory;
            $this->children_nodes[$counter]['duration'] = $row->duration;
            $this->children_nodes[$counter]['price'] = $row->price;
            $this->children_nodes[$counter]['color'] = $row->color;
            $this->children_nodes[$counter]['end_node'] = $row->end_node;
            $this->children_nodes[$counter]['active'] = $row->active;
            $this->children_nodes[$counter]['description'] = $row->description;
            $this->children_nodes[$counter]['image_src'] = $row->image_src;
            $this->children_nodes[$counter]['is_mobile'] = $row->is_mobile;
            $this->children_nodes[$counter]['category_name'] = $row->category ? $row->category->name : null; // Add category name
            $counter++;
        }
    }

    var $nodeList = array();

    /**
     * Convert node tree to a list
     */
    public function toList($tree, $c = 0, $active_only = false)
    {
        if ($tree->id != 0) {
            if ($this->non_negative_groups) {
                $this->nodeList[$tree->id] = array(
                    'id' => $tree->id,
                    'name' => $this->space($c) . $tree->name,
                    'parent_id' => $tree->parent_id,
                    'slug' => $tree->slug,
                    'complimentory' => $tree->complimentory,
                    'active' => $tree->active,
                    'duration' => $tree->duration,
                    'price' => $tree->price,
                    'color' => $tree->color,
                    'end_node' => $tree->end_node,
                    'description' => $tree->description,
                    'image_src' => $tree->image_src,
                    'is_mobile' => $tree->is_mobile,
                    'category_name' => $tree->category_name // Include category name
                );
            } else {
                $this->nodeList[-$tree->id] = array(
                    'id' => $tree->id,
                    'name' => $this->space($c) . $tree->name,
                    'parent_id' => $tree->parent_id,
                    'slug' => $tree->slug,
                    'complimentory' => $tree->complimentory,
                    'active' => $tree->active,
                    'duration' => $tree->duration,
                    'price' => $tree->price,
                    'color' => $tree->color,
                    'end_node' => $tree->end_node,
                    'description' => $tree->description,
                    'image_src' => $tree->image_src,
                    'is_mobile' => $tree->is_mobile,
                    'category_name' => $tree->category_name // Include category name
                );
            }
        } else {
            $this->nodeList[0] = $this->default_text;
        }

        if (count($tree->children_nodes) > 0) {
            $c++;
            foreach ($tree->children_nodes as $id => $data) {
                $node_name = $data['name'];

                $this->nodeList[$data['id']] = array(
                    'id' => $data['id'],
                    'name' => $this->space($c) . $node_name,
                    'parent_id' => $data['parent_id'],
                    'slug' => $data['slug'],
                    'complimentory' => $data['complimentory'],
                    'duration' => $data['duration'],
                    'price' => $data['price'],
                    'color' => $data['color'],
                    'end_node' => $data['end_node'],
                    'active' => $data['active'],
                    'description' => $data['description'],
                    'image_src' => $data['image_src'],
                    'is_mobile' => $data['is_mobile'],
                    'category_name' => $data['category_name'] // Include category name
                );
            }
            $c--;
        }

        foreach ($tree->children_groups as $id => $data) {
            $c++;
            $this->toList($data, $c);
            $c--;
        }
    }

    function space($count)
    {
        $str = '';
        for ($i = 1; $i <= $count; $i++) {
            $str .= '   ';
        }
        return $str;
    }
}