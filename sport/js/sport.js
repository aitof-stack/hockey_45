function news_filter_form()
	{
	let f=document.getElementById('news_filter');
	if (f["year"].value && f["month"].value) f.submit();
	}
function tourn_filter_form(e)
	{
	if (!e.value) return false;
	let id=e.id;
	if (id=='season_id') window.location.href='/sport/competitions?season_id='+e.value;
	else if (id=='tour_id') window.location.href='/sport/competitions/'+e.value;
	else
		{
		let tour_elem=document.getElementById('tour_id');
		if (tour_elem.value) window.location.href='/sport/competitions/'+tour_elem.value+'/'+e.value;
		}
	}
function group_selected(e)
	{
	if (e.value) window.location.href='/sport/calendar/'+e.value;
	}
function game_filter()
	{
	let team1_elem=document.getElementById('team1');
	let team2_elem=document.getElementById('team2');
	let month_elem=document.getElementById('month');
	let group_elem=document.getElementById('group_id');
	let url='/sport/calendar/'+group_elem.value;
	let query=[];
	if (team1_elem.value) query.push('team1='+team1_elem.value);
	if (team2_elem.value) query.push('team2='+team2_elem.value);
	if (month_elem && month_elem.value) query.push('month='+month_elem.value);
	if (query.length) url+='?'+query.join('&');
	window.location.href=url;
	}
function player_match_filter(e)
	{
	let tbl=document.getElementById('player_matches');
	if (!e.value)
		{
		let rows=tbl.querySelectorAll('.filtered');
		for (let row of rows)
			{
			row.classList.remove('filtered');
			}
		}
	else
		{
		let season_and_team=e.value.split('-');
		let year_id=season_and_team[0];
		let team_id=season_and_team[1];
		let rows=tbl.querySelectorAll('tr:not(.filtered[data-year="'+year_id+'"][data-team="'+team_id+'"])');
		for (let row of rows)
			{
			row.classList.add('filtered');
			
			}
		rows=tbl.querySelectorAll('.filtered[data-year="'+year_id+'"][data-team="'+team_id+'"]');
		for (let row of rows)
			{
			row.classList.remove('filtered');
			}
		}
	}
function club_region_filter(e)
	{
	let clubs_block=document.getElementById('clubs');
	if (!e.value)
		{
		let rows=clubs_block.querySelectorAll('.filtered');
		for (let row of rows)
			{
			row.classList.remove('filtered');
			}
		}
	else
		{
		let rows=clubs_block.querySelectorAll('.club-item:not(.filtered[data-region="'+e.value+'"])');
		for (let row of rows)
			{
			row.classList.add('filtered');
			}
		rows=clubs_block.querySelectorAll('.filtered[data-region="'+e.value+'"]');
		for (let row of rows)
			{
			row.classList.remove('filtered');
			}
		}
	}
function ref_colleg_filter(e)
	{
	let clubs_block=document.getElementById('content');
	if (!e.value)
		{
		let rows=clubs_block.querySelectorAll('.filtered');
		for (let row of rows)
			{
			row.classList.remove('filtered');
			}
		}
	else
		{
		let rows=clubs_block.querySelectorAll('div.roster-item:not(.filtered[data-region="'+e.value+'"]), tbody>tr:not(.filtered[data-region="'+e.value+'"])');
		for (let row of rows)
			{
			row.classList.add('filtered');
			}
		rows=clubs_block.querySelectorAll('div.roster-item.filtered[data-region="'+e.value+'"], tbody>tr.filtered[data-region="'+e.value+'"]');
		for (let row of rows)
			{
			row.classList.remove('filtered');
			}
		}
	
		$( 'table.tablesorter' ).trigger( 'update', [ true ] );
	}
function schedule_view(e)
	{
	window.location.href=e.value;
	}

async function oppo_filter()
{
	let f = document.getElementById('filter_opponents');
	let fData = new FormData(f);
	let infoElems = {};
	infoElems.skater = document.getElementById('skater-selected-oppos');
	infoElems.goalkeeper = document.getElementById('goalkeeper-selected-oppos');
	
	infoElems.skater.innerHTML = '';
	infoElems.goalkeeper.innerHTML = '';
	
	let oppoSelectElem = document.getElementById('select2-oppo');
	
	try
	{
		let response=await fetch('/sport/filter_oppo',{
				method: 'POST',
				body: fData
			});
		if (response.status==200)
			{
			let text=await response.text();
			if (text && (json = JSON.parse(text)))
				{
				if (json.error)
					{
					alert('При получении данных произошла ошибка: ' + json.error);
					}
					else
					{
						['skater','goalkeeper'].forEach(function(amplua){
							let tbl = document.getElementById('team_'+amplua+'_stat');
							tbl.tBodies[0].innerHTML = '';
							if (!json[amplua] || !arStatFields[amplua])
								return;
							
							for (let i in json[amplua])
							{
								let plStatRow = json[amplua][i];
								let plId = plStatRow.playerId;
								let plLink = playerInfo[plId] ? "<a href='/sport/players/"+plId+"'>" + playerInfo[plId]["name"] + "</a>" : "";
								let plPos = playerInfo[plId] ? playerInfo[plId]["pos"] : "";
								let strRow = `<td class='place'></td>
									<td class='gt-cell-team sticky'>${plLink}</td>`;
								
								if (amplua == 'skater') strRow += '<td>'+plPos+'</td>';
								
								arStatFields[amplua].forEach(function(statField){
									strRow += "<td class='fields'>" + (plStatRow[statField] ? plStatRow[statField] : "") + "</td>";
								});
								
								let newTR = document.createElement('tr');
								newTR.innerHTML = strRow;
								
								tbl.tBodies[0].append(newTR);
							}
							
							if (oppoSelectElem.options.length)
							{
								for (opt of oppoSelectElem.options)
								{
									if (opt.selected == true)
									{
										if (!infoElems[amplua].innerHTML)
											infoElems[amplua].innerHTML = "<b>Выбранные соперники:</b> ";
										
										infoElems[amplua].innerHTML += "<span>"+opt.text+"</span>";
									}
								}
							}
							
							$(tbl).trigger('update',[true]);
						});
					}
				}
			}
			else
			{
			alert('При получении данных произошла ошибка 2');
			}
	}
	catch(er)
	{
		alert('При получении данных произошла ошибка '+er);
	}
	
}

window.addEventListener('load', function(){
	let arShareElems = document.querySelectorAll('.article-share-content > a');
	if (arShareElems.length)
	{
		let windowUrl = window.location.href;
		for (let elemLink of arShareElems)
		{
			let classes = elemLink.classList;
			let linkUrl = '';
			
			switch (true)
			{
				case classes.contains('vk') :
					linkUrl = 'https://vk.com/share.php?url=' +windowUrl+ '&title=' + encodeURIComponent(document.title);
					break;
				
				case classes.contains('whatsapp') :
					linkUrl = 'https://api.whatsapp.com/send?text=' +windowUrl;
					break;
				
				case classes.contains('telegram') :
					linkUrl = 'https://telegram.me/share/url?url=' +windowUrl;
					break;
					
				default:
					break;
			}
			
			if (linkUrl)
				elemLink.href = linkUrl;
		}
	}
	
	let switchers=document.querySelectorAll('input[name="filter-events"]');
	for (let elem of switchers)
	{
		let keySwitcher = window.location.href + '_' + elem.id;
		
		let isChecked = sessionStorage.getItem(keySwitcher);
		if (isChecked === 'true')
			elem.checked = true;
		
		elem.addEventListener('change',function(){
			let eInputsOther = document.querySelectorAll('input[name="' +this.name+ '"]');
			for (let e of eInputsOther) {
				let currKeySwitcher = window.location.href + '_' + e.id;
				sessionStorage.setItem(currKeySwitcher, e.checked);
			}
		});
	}
});