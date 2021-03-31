<?php
class marker_groupsViewGmp extends viewGmp {
	public function getTabContent() {
		$markerGroups = $this->getModel()->getAllMarkerGroups();

		foreach($markerGroups as $k => $mg) {
			$markerGroups[$k]['actions'] = $this->getListOperations($mg);
		}
		frameGmp::_()->addStyle('admin.mgr', $this->getModule()->getModPath(). 'css/admin.marker.groups.css');
		frameGmp::_()->getModule('templates')->loadJqTreeView();
		frameGmp::_()->addScript('admin.mgr.list', $this->getModule()->getModPath(). 'js/admin.marker_groups.list.js');
		frameGmp::_()->addJSVar('admin.mgr.list', 'mgrTblData', $markerGroups);
		return parent::getContent('mgrAdmin');
	}
	public function getEditMarkerGroup($id = 0) {
		frameGmp::_()->addScript('admin.mgr.edit', $this->getModule()->getModPath(). 'js/admin.marker_groups.edit.js');
		frameGmp::_()->addStyle('admin.mgr', $this->getModule()->getModPath() . 'css/admin.marker.groups.css');
		$editMarkerGroup = $id ? true : false;

		if($editMarkerGroup) {
			$markerGroup = $this->getModel()->getMarkerGroupById( $id );
			$this->assign('marker_group', $markerGroup);
			frameGmp::_()->addJSVar('admin.mgr.edit', 'mgrMarkerGroup', $markerGroup);
		}
		$this->assign('editMarkerGroup', $editMarkerGroup);
		$this->assign('parentsList', $this->getModel()->getMarkerGroupsForSelect(array( 0 => __('None', GMP_LANG_CODE) )));
		return parent::getContent('mgrEditMarkerGroup');
	}
	public function getOptionsTabContent() {
		frameGmp::_()->addScript('jquery-ui-sortable');
		frameGmp::_()->addScript('admin.mgr.edit', $this->getModule()->getModPath(). 'js/admin.marker_groups.options.js');
		frameGmp::_()->addStyle('admin.mgr', $this->getModule()->getModPath() . 'css/admin.marker.groups.css');
		$this->assign('options', $this->getModel()->getMarkerGroupsOptions());
		return parent::getContent('mgrMarkerGroupsOptions');
	}
	public function getListOperations($markerGroup) {
		$this->assign('marker_group', $markerGroup);
		$this->assign('editLink', $this->getModule()->getEditMarkerGroupLink( $markerGroup['id'] ));
		return trim(parent::getInlineContent('mgrListOperations'));
	}
	public function _getPageLink($name) {
		return frameGmp::_()->getModule('options')->getTabUrl($name);
	}
}