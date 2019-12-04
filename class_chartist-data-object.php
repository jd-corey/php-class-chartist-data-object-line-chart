<?
/* ------------------------------------------------------------------------------------------
PHP Class "cdo: Chartist Data Object" (to be used in combination with Chartist Line Charts)

Purpose: Provide simple functionality for wrapping data, so that Chartist.JS can handle it.
That is, the class wraps data that shall be displayed on xaxis/ yaxis of a Charist line chart.
Displaying development of product prices per day is implemented, other types might follow.

Chartist expects the following data model to be used for rendering its Line Charts:
		labels: [1, 2, 3, 4],						<= Data for xaxis, i.e. date (d.m.y), weekday or alike
		series: [												
							[100, 120, 180, 200],	<= Price of a product as per the respective day/ date
							[150, 180, 190, 195]	<= One "array" for each of the products' retailers
						]
------------------------------------------------------------------------------------------ */

// ------------------------------------------------------------------------------------------
// Class definition: Variables, constructor and methods
// ------------------------------------------------------------------------------------------
final class cdo
{
	// ----------------------------------------------------------------------------------------
	// Variables needed for constructing datasource objects
	// ----------------------------------------------------------------------------------------
	private $input_data;			// Array: Contains the data points for both xaxis and yaxis
	private $axis_types;			// Array: Contains Strings concerning type of axis, e.g. "days"
	private $axis_titles;			// Array: Contains title of xaxis and of yaxis (each as String) 
	
	private $labels_array;		// Array: Bundles value for xaxis (to be returned by getMethod)
	private $labels;					// String: Comma separated values to be shown on  xaxis
			
	private $series_array;		// Array: Bundles values, minimum and maximum for yaxis
	private $series;					// String: Comma separated values to be shown on yaxis 
	private $minimum;					// Integer: Lowest value of data points on yaxis
	private $maximum;					// Integer: Highest value of data points on yaxis
	// ----------------------------------------------------------------------------------------

	// ----------------------------------------------------------------------------------------
	// Constructor method
	// ----------------------------------------------------------------------------------------
	public function __construct($input_data, $axis_types, $axis_titles)
	{
		if (is_array($input_data) && is_array($axis_types) && is_array($axis_titles))
		{
			$this->input_data			=	$input_data;
			$this->axis_types			=	$axis_types;
					
			$this->labels_array		=	array();
			$this->labels					=	"";
					
			$this->series_array		=	array();
			$this->series					=	"";
			$this->minimum				=	0;
			$this->maximum				=	0;
		}
	}
	// ----------------------------------------------------------------------------------------
	
	// ----------------------------------------------------------------------------------------
	//	getLabels(): Get all data needed to fill Chartist's xaxis variable
	// ----------------------------------------------------------------------------------------
	public function getLabels()
	{
		try
		{
			switch ($this->axis_types['xaxis'])
			{
				// Case "days": The xAxis is structured by date series such as 01.01.20, 02.01.20, 03.01.20, ...
				case "days":
					foreach ($this->input_data['xaxis'] as $key => $value)
					{
						if ($key === 0)
						{
							$this->labels			=			"[" . "'" . $value . "'";
						}
						else
						{
							$this->labels			.=		", " . "'" . $value . "'";
						}
					}
					$this->labels					.=		"]";
					break;
			}
					
			$this->labels_array['data'] = $this->labels;
		}
		catch(Exception $e)
		{
			echo "Error!: " . $e->getMessage() . "<br/>";
		  die();
		}
		
		return $this->labels_array;
	}
	// ----------------------------------------------------------------------------------------
	
	// ----------------------------------------------------------------------------------------
	// getSeries(): Get all data needed to fill Chartist's yaxis variable
	// ----------------------------------------------------------------------------------------
	public function getSeries()
	{
		try
		{
			switch ($this->axis_types['yaxis'])
			{
				case "prices":
				
					/* ----------------------------------------------------------------------------------
					Case "Prices": Development of product prices per Retailer will be shown
					Data model of $chart_data (which is equal to input_data['yaxis'])
							- $chart_data[$key][$z]['organisation_name']			String: Name of retailer
							- $chart_data[$key][$z]['validity_date']					Date: Date of price (y.m.d)
							- $chart_data[$key][$z]['price']									String: Product price itself
					// --------------------------------------------------------------------------------*/
					
					$chart_data	= $this->input_data['yaxis'];
					
					foreach ($chart_data as $key => $value)		// Array might contain several product retailers
					{
						if (!empty($series))
						{
							$series						=			$series.", ";
						}
								
						$cnt_price					=			0;
						$retailer						=			"";
						$days_done					=			array();
								
						foreach ($this->input_data['xaxis'] as $day)		// For each day according to input data
						{
							for ($z=0; $z < count($chart_data[$key]); $z++)		// For each data point of the retailer
							{
								$retailer			=			$chart_data[$key][$z]['organisation_name'];
								
								if (strcmp($day, $chart_data[$key][$z]['validity_date']) === 0 AND !in_array($day, $days_done))
								{
									$price				=			$chart_data[$key][$z]['price'];
									$prices[]			=			$chart_data[$key][$z]['price'];
									$days_done[]	=			$day;
								}
								elseif (!in_array($day, $days_done))
								{
									$price				=			"null";
								}
							}
									
							if ($cnt_price == 0)
							{
								$series					=			$series."[";
							}
							else
							{
								$series					=			$series.", ";
							}

							// If Chartist's meta information (tool tip) is used, one needs the "meta" attribute
							$series						=			$series."{meta: '".$retailer."', value: ".$price."}";
							$cnt_price++;
						}
						$series							=			$series . "]";
						$this->series				=			$series;
					}
					// ----------------------------------------------------------------------------------
					
					// ----------------------------------------------------------------------------------
					// Minimum & Maximum: Calculate lowest and highest value of values on yaxis
					// Input: Array containing all prices => Output: Two Integer variables
					// ----------------------------------------------------------------------------------
					$minimum							=			min($prices)-1;
					$maximum							=			ceil(max($prices)+1);
					if (preg_match("/./", $minimum))
					{
						$min_array					=			explode(".", $minimum);
						$minimum						=			$min_array[0];
					}
					if (preg_match("/./", $maximum))
					{
						$max_array					=			explode(".", $maximum);
						$maximum						=			$max_array[0];
					}
					if ($minimum > $maximum)
					{
						$maximum						=			$minimum+2;
					}
					// ----------------------------------------------------------------------------------
						
					// ----------------------------------------------------------------------------------
					// Bundle data into series_array to be returned by method
					// ----------------------------------------------------------------------------------
					$this->series_array['data']			=	$this->series;
					$this->series_array['minimum']	=	$minimum;
					$this->series_array['maximum']	=	$maximum;
					// ----------------------------------------------------------------------------------
							
					break;
			}
		}
		catch(Exception $e)
		{
			echo "Error!: " . $e->getMessage() . "<br/>";
		  die();
		}
		
		return $this->series_array;
	}
	// ----------------------------------------------------------------------------------------
}
// ------------------------------------------------------------------------------------------
?>