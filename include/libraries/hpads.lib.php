<?php
function hpads_display($area)
{
	$query = 'SELECT * FROM hp_ads WHERE area = "' . $area . '" AND credits > 0 AND expire > "' . time() . '"';
	$result = mysql_query($query) or die(mysql_error());

	while($data = mysql_fetch_assoc($result))
	{
		$ads[$data['id']] = $data;
		for($i = 0; $i < $data['probability']; $i++)
		{
			$pool[] = $data['id'];
		}
	}
	
	
	
	if(count($ads) == 0)
	{
		return false;
		/* Testenvironment debug-ads.
		switch ($area) {
			case 'bigbanner':
				return '<h1 style="background: #eee; width: 728px; height: 90px; line-height: 90px; text-align: center;">bigbanner (90x728)</h1>';
				break;
			case 'modulruta':
				return '<h1 style="background: #eee; width: 200px; height: 200px; line-height: 100px; text-align: center;">modulruta (200x200)</h1>';
				break;
			case 'stortavla':
				return '<h1 style="background: #eee; width: 200px; height: 600px; line-height: 300px; text-align: center;">stortavla (max 200x600)</h1>';
				break;
			default:
				break;
		}*/
	}
	
	$ad = $ads[$pool[array_rand($pool, 1)]];
	
	$query = 'UPDATE hp_ads SET credits = credits - 1, impressions = impressions + 1 WHERE id = "' . $ad['id'] . '" LIMIT 1';
	mysql_query($query);
	
	return $ad['html'];
}

function hpads_form()
{
	$html .= '<div id="hpads_admin">' . "\n";
	$html .= '<h2>Skapa ny annons</h2>' . "\n";
	$html .= '<form method="post">' . "\n";
	$html .= '<input type="hidden" value="create" name="action" />' . "\n";
	$html .= '<label>Namn</label>' . "\n";
	$html .= '<input type="text" name="name" />' . "\n";
	$html .= '<label>Placering</label>' . "\n";
	$html .= '<input type="text" name="area" />' . "\n";
	$html .= '<label>Återstående visningar</label>' . "\n";
	$html .= '<input type="text" name="credits" />' . "\n";
	$html .= '<label>Deadline</label>' . "\n";
	$html .= '<input type="text" name="expire" value="' . date('Y-m-d H:i:s', time() + 86400*7) . '" />' . "\n";
	$html .= '<label>Vikt (0-100)</label>' . "\n";
	$html .= '<input type="text" name="probability" />' . "\n";
	$html .= '<label>Kod</label>' . "\n";
	$html .= '<textarea name="html"></textarea>' . "\n";
	$html .= '<input type="submit" value="Spara" />' . "\n";
	$html .= '</form>' . "\n";
	
	
	$query = 'SELECT * FROM hp_ads ORDER BY credits DESC';
	$result = mysql_query($query);
	$html .= '<h2>Existerande anonser</h2>' . "\n";
	$html .= '<p>OBS! Varje annons sparas individuellt, således: ändra bara en annons åt gången!</p>' . "\n";
	$html .= '<ul>' . "\n";
	while($data = mysql_fetch_assoc($result))
	{
		$html .= '<li>' . "\n";
		$html .= '<div class="summary">' . "\n";
		$html .= '<h4>' . $data['name'] . '</h4>' . "\n";
		$class = ($data['expire'] > time() && $data['credits'] > 0) ? ' class="active"' : '';
		$html .= '<dl' . $class . '><dt>Visningar</dt><dd>' . $data['impressions'] . ' (' . round(($data['impressions']/($data['credits']+$data['impressions']))*100) . '%)</dd>' . "\n";
		$html .= '<dt>Klick</dt><dd>' . $data['clicks'] . '</dd>' . "\n";
		$html .= '<dt>Deadline</dt><dd>' . date('Y-m-d H:i:s', $data['expire']) . '</dd></dl>' . "\n";
		$html .= '</div>' . "\n";

		$html .= '<form method="post">' . "\n";
		$html .= '<input type="hidden" value="update" name="action" />' . "\n";
		$html .= '<input type="hidden" value="' . $data['id'] . '" name="id" />' . "\n";
		$html .= '<label>Namn</label>' . "\n";
		$html .= '<input type="text" name="name" value="' . $data['name'] . '" />' . "\n";

		$html .= '<label>Placering</label>' . "\n";
		$html .= '<input type="text" name="area" value="' . $data['area'] . '" />' . "\n";

		$html .= '<label>Återstående visningar</label>' . "\n";
		$html .= '<input type="text" name="credits" value="' . $data['credits'] . '" />' . "\n";

		$html .= '<label>Deadline</label>' . "\n";
		$html .= '<input type="text" name="expire" value="' . date('Y-m-d H:i:s', $data['expire']) . '" />' . "\n";

		$html .= '<label>Vikt (0-100)</label>' . "\n";
		$html .= '<input type="text" name="probability" value="' . $data['probability'] . '" />' . "\n";

		$html .= '<label>Kod</label>' . "\n";
		$html .= '<textarea name="html">' . htmlspecialchars($data['html']) . '</textarea>' . "\n";

		$html .= '<label>Klicklänk</label>' . "\n";
		$html .= '<p>http://www.hamsterpaj.net/hpads_redir.php?ad=' . $data['uniqid'] . '</p>' . "\n";
		
		$html .= '<input type="submit" value="Uppdatera" />' . "\n";
		$html .= '</form>' . "\n";

		$html .= '</li>';
	}
	$html .= '</ul>' . "\n";

	$html .= '</div>' . "\n";
	return $html;
}

?>