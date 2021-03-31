<section>
	<div class="supsystic-item supsystic-panel">
		<div id="containerWrapper">
			<div id="gmpMgrTab" class="mgrTabContent">
				<a class="button button-table-action" id="addMarkerGroup" href="<?php echo $this->_getPageLink('marker_groups_add_new')?>">
					<?php _e('Add Category', GMP_LANG_CODE)?>
				</a>
				<button class="button" id="gmpMgrSaveBtn">
					<i class="fa fa-save"></i>
					<?php _e('Save', GMP_LANG_CODE)?>
				</button>
				<div style="clear: both;"></div>
				<form id="gmpMgrForm">
					<table class="form-table">
						<tr style="border-bottom: none;">
							<th scope="row">
								<h3><?php _e('Categories Levels', GMP_LANG_CODE)?></h3>
							</th>
							<td>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="marker_group_title"><?php _e('Font Size', GMP_LANG_CODE)?>:</label>
								<i style="float: right;" class="fa fa-question supsystic-tooltip" title="<?php _e('Set font size in pixels for every level of Categories Tree starting from first level.', GMP_LANG_CODE); ?>"></i>
							</th>
							<td>
								<a href="#" class="button mgrFontSizeLevelAddBtn">
									<i class="fa fa-plus"></i>
									<?php _e('Add Level', GMP_LANG_CODE)?>
								</a>
								<a href="#" class="button mgrFontSizeLevelClearBtn">
									<i class="fa fa-eraser"></i>
									<?php _e('Clear', GMP_LANG_CODE)?>
								</a>
								<div id="mgrFontSizeLevelExample" class="mgrFontSizeLevel" style="display: none;">
									<i class="fa fa-arrows-v mgrMoveHandle"></i>
									<input type="text" name="options[lvl_font_size][]" placeholder="14px" disabled="disabled" />
									<a href="#" class="button mgrFontSizeLevelRemoveBtn" title="<?php _e('Remove', GMP_LANG_CODE)?>">
										<i class="fa fa-trash-o"></i>
									</a>
								</div>
								<div id="mgrFontSizeLevelsOuterShell">
									<div id="mgrFontSizeLevelsShell">
										<?php if(!empty($this->options['lvl_font_size'])) {?>
											<?php foreach($this->options['lvl_font_size'] as $l) {?>
												<div class="mgrFontSizeLevel">
													<i class="fa fa-arrows-v mgrMoveHandle"></i>
													<input type="text" name="options[lvl_font_size][]" value="<?php echo $l?>" placeholder="14" />
													<a href="#" class="button mgrFontSizeLevelRemoveBtn" title="<?php _e('Remove', GMP_LANG_CODE)?>">
														<i class="fa fa-trash-o"></i>
													</a>
												</div>
											<?php }?>
										<?php }?>
									</div>
								</div>
							</td>
						</tr>
					</table>
					<?php echo htmlGmp::hidden('mod', array('value' => 'marker_groups'))?>
					<?php echo htmlGmp::hidden('action', array('value' => 'saveMarkerGroupsOptions'))?>
				</form>
				<div style="clear: both;"></div>
			</div>
		</div>
	</div>
</section>