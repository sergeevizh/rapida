{* Вкладки *}
{capture name=tabs}
	{if isset($userperm['users'])}<li><a href="?module=UsersAdmin">Покупатели</a></li>{/if}
	<li class="active"><a href="?module=GroupsAdmin">Группы</a></li>		
	{if isset($userperm['coupons'])}<li><a href="?module=CouponsAdmin">Купоны</a></li>{/if}
{/capture}

{* Title *}
{$meta_title='Группы пользователей' scope=root}

{* Заголовок *}
<div id="header">
	<h1>Группы пользователей</h1> 
	<a class="add" href="?module=GroupAdmin">Добавить группу</a>
</div>	


<!-- Основная часть -->
<div id="main_list">

	<form id="list_form" method="post">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">
	{if !empty($groups)}
	<div id="list" class="groups">
		{foreach $groups as $group}
		<div class="row">
		 	<div class="checkbox cell">
				<input type="checkbox" name="check[]" value="{$group['id']}"/>				
			</div>
			<div class="group_name cell">
				<a href="?module=GroupAdmin&id={$group['id']}">{$group['name']}</a>
			</div>
			<div class="group_discount cell">
				{$group['discount']} %
			</div>
			<div class="icons cell">
				<a class="delete" title="Удалить" href="#"></a>
			</div>
			<div class="clear"></div>
		</div>
		{/foreach}
	</div>
	{/if}
	
	<div id="action">
	<label id="check_all" class="dash_link">Выбрать все</label>

	<span id=select>
	<select name="action">
		<option value="delete">Удалить</option>
	</select>
	</span>

	<input id="apply_action" class="button_green" type="submit" value="Применить">
	</div>


	</form>

</div>


{literal}
<script>
$(function() {

	// Раскраска строк
	function colorize()
	{
		$("#list div.row:even").addClass('even');
		$("#list div.row:odd").removeClass('even');
	}
	// Раскрасить строки сразу
	colorize();
	
	// Выделить все
	$("#check_all").click(function() {
		$('#list input[type="checkbox"][name*="check"]').attr('checked', 1-$('#list input[type="checkbox"][name*="check"]').attr('checked'));
	});	

	// Удалить 
	$("a.delete").click(function() {
		$('#list input[type="checkbox"][name*="check"]').attr('checked', false);
		$(this).closest(".row").find('input[type="checkbox"][name*="check"]').attr('checked', true);
		$(this).closest("form").find('select[name="action"] option[value=delete]').attr('selected', true);
		$(this).closest("form").submit();
	});
		
	// Подтверждение удаления
	$("form").submit(function() {
		if($('#list input[type="checkbox"][name*="check"]:checked').length>0)
			if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
				return false;	
	});
	
});

</script>
{/literal}
