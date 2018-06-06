<div class="tab-content">
	<input type="hidden" v-model="settings_page_json_string" name="case27_listing_type_settings_page">

	<div class="listing-type-settings">
		<div class="column">
			<div class="card">
				<h4>General</h4>

				<div class="form-group">
					<label>Icon</label>
					<iconpicker v-model="settings.icon"></iconpicker>
				</div>

				<div class="form-group">
					<label>Singular name <small>(e.g. Business)</small></label>
					<input type="text" v-model="settings.singular_name">
				</div>

				<div class="form-group">
					<label>Plural name <small>(e.g. Businesses)</small></label>
					<input type="text" v-model="settings.plural_name">
				</div>
			</div>

			<div class="card">
				<h4>Tools</h4>
				<div class="form-group">
					<label>Listing type configuration</label><br>
					<a @click.prevent="exportConfig" class="btn btn-primary">Export config file</a>
					<a @click.prevent="startImportConfig" class="btn btn-plain">Import config file</a>
					<input type="file" name="c27-import-config" id="c27-import-config" @change="importConfig"
					onclick="return confirm('Imported configuration will overwrite your current settings. Do you want to proceed?')">
				</div>
			</div>
		</div>
	</div>
</div>

<!-- <pre>{{ settings }}</pre> -->
