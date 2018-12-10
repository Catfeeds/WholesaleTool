<?php
// +---------------------------------------------------------------------+
// | OneBase    | [ WE CAN DO IT JUST THINK ]                            |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | Bigotry <3162875@qq.com>                               |
// +---------------------------------------------------------------------+
// | Repository | https://gitee.com/Bigotry/OneBase                      |
// +---------------------------------------------------------------------+

namespace app\admin\controller;

use think\Loader;

/**
 * 小程序-用户控制器
 */
class User extends AdminBase
{

    /**
     * 会员列表
     */
    public function userList1()
    {
        if( $this->param ){
            $validate = Loader::validate( 'User' );
            if( !$validate->scene( 'list' )->check( $this->param ) ){
                $this->jump( RESULT_ERROR,$validate->getError() );
            }
        }

        $where = $this->logicMember->getWhere( $this->param );

        $this->assign( 'list',$this->logicMember->getMemberList( $where ) );

        return $this->fetch( 'user_list' );
    }

    /**
     * 会员列表
     */
    public function userList()
    {
        if( $this->param ){
            $validate = Loader::validate( 'User' );
            if( !$validate->scene( 'list' )->check( $this->param ) ){
                $this->jump( RESULT_ERROR,$validate->getError() );
            }
        }
        $where = $this->logicUser->getWhere( $this->param );
        $this->assign( 'list',$this->logicUser->getUserList( $where ) );
        return $this->fetch( 'user_list' );
    }

    /**
     * 会员导出
     */
    public function exportMemberList()
    {

        $where = $this->logicMember->getWhere( $this->param );

        $this->logicMember->exportMemberList( $where );
    }

    /**
     * 会员添加
     */
    public function userAdd()
    {
        IS_POST && $this->jump( $this->logicUser->userAdd( $this->param ) );

        return $this->fetch( 'user_add' );
    }

    /**
     * 会员编辑
     */
    public function userEdit()
    {

        IS_POST && $this->jump( $this->logicMember->memberEdit( $this->param ) );

        $info = $this->logicMember->getMemberInfo( [ 'id' => $this->param[ 'id' ] ] );

        $this->assign( 'info',$info );

        return $this->fetch( 'user_edit' );
    }

    /**
     * 会员删除
     */
    public function del( $id = 0 )
    {
        return $this->jump( $this->logicUser->del( $id ) );
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {

        IS_POST && $this->jump( $this->logicMember->editPassword( $this->param ) );

        $info = $this->logicMember->getMemberInfo( [ 'id' => MEMBER_ID ] );

        $this->assign( 'info',$info );

        return $this->fetch( 'edit_password' );
    }
}
