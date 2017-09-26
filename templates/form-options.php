<div id="sms-sortables" class="meta-box-sortables ui-sortable">
<h3>Dolibarr synchronisation</h3>
<fieldset>
<legend>In the following fields, you can use these tags:<br />
	<?php $data['form']->suggest_mail_tags(); ?>
</legend> 
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row">
		  	<label for="wpcf7-dolibarr-api-url">API URL:</label>
		</th>
		<td>
		  	<input type="text" id="wpcf7-dolibarr-api-url" name="wpcf7-dolibarr[api_url]" class="wide" size="70" value="<?php echo $data['api_url']; ?>">
		</td>
	</tr>
	
	<tr>
		<th scope="row">
		  	<label for="wpcf7-dolibarr-api-key">API key:</label>
		</th>
		<td>
		  	<input type="text" id="wpcf7-dolibarr-api-key" name="wpcf7-dolibarr[api_key]" class="wide" size="70" value="<?php echo $data['api_key']; ?>">
		</td>
	</tr>

	<tr>
		<th scope="row">
		  	<label for="wpcf7-dolibarr-field-category-id">Customer category ID:</label>
		</th>
		<td>
		  	<input type="text" id="wpcf7-dolibarr-field-category-id" name="wpcf7-dolibarr[field_category_id]" class="wide" size="70" value="<?php echo $data['field_category_id']; ?>">
		</td>
	</tr>

	
	<tr>
		<th scope="row">
		  	<label for="wpcf7-dolibarr-field-company">Tag for company name:</label>
		</th>
		<td>
		  	<input type="text" id="wpcf7-dolibarr-field-company" name="wpcf7-dolibarr[field_company]" class="wide" size="70" value="<?php echo $data['field_company']; ?>">
		</td>
	</tr>
	
	<tr>
		<th scope="row">
		  	<label for="wpcf7-dolibarr-field-email">Tag for email:</label>
		</th>
		<td>
		  	<input type="text" id="wpcf7-dolibarr-field-email" name="wpcf7-dolibarr[field_email]" class="wide" size="70" value="<?php echo $data['field_email']; ?>">
		</td>
	</tr>
	
	<tr>
		<th scope="row">
		  	<label for="wpcf7-dolibarr-field-lastname">Tag for lastname:</label>
		</th>
		<td>
		  	<input type="text" id="wpcf7-dolibarr-field-lastname" name="wpcf7-dolibarr[field_lastname]" class="wide" size="70" value="<?php echo $data['field_lastname']; ?>">
		</td>
	</tr>
	
	<tr>
		<th scope="row">
		  	<label for="wpcf7-dolibarr-field-firstname">Tag for firstname:</label>
		</th>
		<td>
		  	<input type="text" id="wpcf7-dolibarr-field-firstname" name="wpcf7-dolibarr[field_firstname]" class="wide" size="70" value="<?php echo $data['field_firstname']; ?>">
		</td>
	</tr>
	
	
	</tbody>
</table>
</fieldset>
