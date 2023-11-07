<?php

declare(strict_types=1);
/**
 * Page class.
 *
 * @package   YetiForcePDF\Document
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF;

use YetiForcePDF\Layout\Box;
use YetiForcePDF\Layout\Coordinates\Coordinates;
use YetiForcePDF\Layout\Dimensions\BoxDimensions;
use YetiForcePDF\Layout\Dimensions\Dimensions;
use YetiForcePDF\Layout\FooterBox;
use YetiForcePDF\Layout\HeaderBox;
use YetiForcePDF\Layout\TableFooterGroupBox;
use YetiForcePDF\Layout\TableHeaderGroupBox;
use YetiForcePDF\Layout\TableWrapperBox;
use YetiForcePDF\Layout\TextBox;
use YetiForcePDF\Layout\WatermarkBox;
use YetiForcePDF\Objects\Basic\StreamObject;

/**
 * Class Page.
 */
class Page extends \YetiForcePDF\Objects\Basic\DictionaryObject
{
	/**
	 * @var int
	 */
	protected $group = 0;
	/**
	 * {@inheritdoc}
	 */
	protected $dictionaryType = 'Page';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Page';

	/**
	 * @var int page number
	 */
	protected $pageNumber = 1;
	/**
	 * @var int page count
	 */
	protected $pageCount = 1;
	/**
	 * Page resources.
	 *
	 * @var array
	 */
	protected $resources = [];
	/**
	 * Page content streams.
	 *
	 * @var StreamObject
	 */
	protected $contentStream;
	/**
	 * Portrait page orientation.
	 */
	const ORIENTATION_PORTRAIT = 'P';
	/**
	 * Landscape page orientation.
	 */
	const ORIENTATION_LANDSCAPE = 'L';
	/**
	 * After page breaking box was cut below.
	 */
	const CUT_BELOW = 1;
	/**
	 * After page breaking box was cut above.
	 */
	const CUT_ABOVE = 2;
	/**
	 * Current page format.
	 *
	 * @var string
	 */
	protected $format;
	/**
	 * Current page orientation.
	 *
	 * @var string
	 */
	protected $orientation;
	/**
	 * User unit - to calculate page dpi.
	 *
	 * @var float
	 */
	protected $userUnit = 1.0;
	/**
	 * Page margins.
	 *
	 * @var array
	 */
	protected $margins;
	/**
	 * Page dimensions.
	 *
	 * @var BoxDimensions
	 */
	protected $dimensions;
	/**
	 * Page outer dimensions.
	 *
	 * @var BoxDimensions
	 */
	protected $outerDimensions;
	/**
	 * @var Coordinates
	 */
	protected $coordinates;
	/**
	 * Don't group this 'group' names.
	 *
	 * @var string[]
	 */
	protected $doNotGroup = [];
	/**
	 * @var Box main box with content
	 */
	protected $box;

	public static $pageFormats = [
		// ISO 216 A Series + 2 SIS 014711 extensions
		'A0' => [2383.937, 3370.394], // = (  841 x 1189 ) mm  = ( 33.11 x 46.81 ) in
		'A1' => [1683.780, 2383.937], // = (  594 x 841  ) mm  = ( 23.39 x 33.11 ) in
		'A2' => [1190.551, 1683.780], // = (  420 x 594  ) mm  = ( 16.54 x 23.39 ) in
		'A3' => [841.890, 1190.551], // = (  297 x 420  ) mm  = ( 11.69 x 16.54 ) in
		'A4' => [595.276, 841.890], // = (  210 x 297  ) mm  = (  8.27 x 11.69 ) in
		'A5' => [419.528, 595.276], // = (  148 x 210  ) mm  = (  5.83 x 8.27  ) in
		'A6' => [297.638, 419.528], // = (  105 x 148  ) mm  = (  4.13 x 5.83  ) in
		'A7' => [209.764, 297.638], // = (   74 x 105  ) mm  = (  2.91 x 4.13  ) in
		'A8' => [147.402, 209.764], // = (   52 x 74   ) mm  = (  2.05 x 2.91  ) in
		'A9' => [104.882, 147.402], // = (   37 x 52   ) mm  = (  1.46 x 2.05  ) in
		'A10' => [73.701, 104.882], // = (   26 x 37   ) mm  = (  1.02 x 1.46  ) in
		'A11' => [51.024, 73.701], // = (   18 x 26   ) mm  = (  0.71 x 1.02  ) in
		'A12' => [36.850, 51.024], // = (   13 x 18   ) mm  = (  0.51 x 0.71  ) in
		// ISO 216 B Series + 2 SIS 014711 extensions
		'B0' => [2834.646, 4008.189], // = ( 1000 x 1414 ) mm  = ( 39.37 x 55.67 ) in
		'B1' => [2004.094, 2834.646], // = (  707 x 1000 ) mm  = ( 27.83 x 39.37 ) in
		'B2' => [1417.323, 2004.094], // = (  500 x 707  ) mm  = ( 19.69 x 27.83 ) in
		'B3' => [1000.630, 1417.323], // = (  353 x 500  ) mm  = ( 13.90 x 19.69 ) in
		'B4' => [708.661, 1000.630], // = (  250 x 353  ) mm  = (  9.84 x 13.90 ) in
		'B5' => [498.898, 708.661], // = (  176 x 250  ) mm  = (  6.93 x 9.84  ) in
		'B6' => [354.331, 498.898], // = (  125 x 176  ) mm  = (  4.92 x 6.93  ) in
		'B7' => [249.449, 354.331], // = (   88 x 125  ) mm  = (  3.46 x 4.92  ) in
		'B8' => [175.748, 249.449], // = (   62 x 88   ) mm  = (  2.44 x 3.46  ) in
		'B9' => [124.724, 175.748], // = (   44 x 62   ) mm  = (  1.73 x 2.44  ) in
		'B10' => [87.874, 124.724], // = (   31 x 44   ) mm  = (  1.22 x 1.73  ) in
		'B11' => [62.362, 87.874], // = (   22 x 31   ) mm  = (  0.87 x 1.22  ) in
		'B12' => [42.520, 62.362], // = (   15 x 22   ) mm  = (  0.59 x 0.87  ) in
		// ISO 216 C Series + 2 SIS 014711 extensions + 5 EXTENSION
		'C0' => [2599.370, 3676.535], // = (  917 x 1297 ) mm  = ( 36.10 x 51.06 ) in
		'C1' => [1836.850, 2599.370], // = (  648 x 917  ) mm  = ( 25.51 x 36.10 ) in
		'C2' => [1298.268, 1836.850], // = (  458 x 648  ) mm  = ( 18.03 x 25.51 ) in
		'C3' => [918.425, 1298.268], // = (  324 x 458  ) mm  = ( 12.76 x 18.03 ) in
		'C4' => [649.134, 918.425], // = (  229 x 324  ) mm  = (  9.02 x 12.76 ) in
		'C5' => [459.213, 649.134], // = (  162 x 229  ) mm  = (  6.38 x 9.02  ) in
		'C6' => [323.150, 459.213], // = (  114 x 162  ) mm  = (  4.49 x 6.38  ) in
		'C7' => [229.606, 323.150], // = (   81 x 114  ) mm  = (  3.19 x 4.49  ) in
		'C8' => [161.575, 229.606], // = (   57 x 81   ) mm  = (  2.24 x 3.19  ) in
		'C9' => [113.386, 161.575], // = (   40 x 57   ) mm  = (  1.57 x 2.24  ) in
		'C10' => [79.370, 113.386], // = (   28 x 40   ) mm  = (  1.10 x 1.57  ) in
		'C11' => [56.693, 79.370], // = (   20 x 28   ) mm  = (  0.79 x 1.10  ) in
		'C12' => [39.685, 56.693], // = (   14 x 20   ) mm  = (  0.55 x 0.79  ) in
		'C76' => [229.606, 459.213], // = (   81 x 162  ) mm  = (  3.19 x 6.38  ) in
		'DL' => [311.811, 623.622], // = (  110 x 220  ) mm  = (  4.33 x 8.66  ) in
		'DLE' => [323.150, 637.795], // = (  114 x 225  ) mm  = (  4.49 x 8.86  ) in
		'DLX' => [340.158, 666.142], // = (  120 x 235  ) mm  = (  4.72 x 9.25  ) in
		'DLP' => [280.630, 595.276], // = (   99 x 210  ) mm  = (  3.90 x 8.27  ) in (1/3 A4)
		// SIS 014711 E Series
		'E0' => [2491.654, 3517.795], // = (  879 x 1241 ) mm  = ( 34.61 x 48.86 ) in
		'E1' => [1757.480, 2491.654], // = (  620 x 879  ) mm  = ( 24.41 x 34.61 ) in
		'E2' => [1247.244, 1757.480], // = (  440 x 620  ) mm  = ( 17.32 x 24.41 ) in
		'E3' => [878.740, 1247.244], // = (  310 x 440  ) mm  = ( 12.20 x 17.32 ) in
		'E4' => [623.622, 878.740], // = (  220 x 310  ) mm  = (  8.66 x 12.20 ) in
		'E5' => [439.370, 623.622], // = (  155 x 220  ) mm  = (  6.10 x 8.66  ) in
		'E6' => [311.811, 439.370], // = (  110 x 155  ) mm  = (  4.33 x 6.10  ) in
		'E7' => [221.102, 311.811], // = (   78 x 110  ) mm  = (  3.07 x 4.33  ) in
		'E8' => [155.906, 221.102], // = (   55 x 78   ) mm  = (  2.17 x 3.07  ) in
		'E9' => [110.551, 155.906], // = (   39 x 55   ) mm  = (  1.54 x 2.17  ) in
		'E10' => [76.535, 110.551], // = (   27 x 39   ) mm  = (  1.06 x 1.54  ) in
		'E11' => [53.858, 76.535], // = (   19 x 27   ) mm  = (  0.75 x 1.06  ) in
		'E12' => [36.850, 53.858], // = (   13 x 19   ) mm  = (  0.51 x 0.75  ) in
		// SIS 014711 G Series
		'G0' => [2715.591, 3838.110], // = (  958 x 1354 ) mm  = ( 37.72 x 53.31 ) in
		'G1' => [1919.055, 2715.591], // = (  677 x 958  ) mm  = ( 26.65 x 37.72 ) in
		'G2' => [1357.795, 1919.055], // = (  479 x 677  ) mm  = ( 18.86 x 26.65 ) in
		'G3' => [958.110, 1357.795], // = (  338 x 479  ) mm  = ( 13.31 x 18.86 ) in
		'G4' => [677.480, 958.110], // = (  239 x 338  ) mm  = (  9.41 x 13.31 ) in
		'G5' => [479.055, 677.480], // = (  169 x 239  ) mm  = (  6.65 x 9.41  ) in
		'G6' => [337.323, 479.055], // = (  119 x 169  ) mm  = (  4.69 x 6.65  ) in
		'G7' => [238.110, 337.323], // = (   84 x 119  ) mm  = (  3.31 x 4.69  ) in
		'G8' => [167.244, 238.110], // = (   59 x 84   ) mm  = (  2.32 x 3.31  ) in
		'G9' => [119.055, 167.244], // = (   42 x 59   ) mm  = (  1.65 x 2.32  ) in
		'G10' => [82.205, 119.055], // = (   29 x 42   ) mm  = (  1.14 x 1.65  ) in
		'G11' => [59.528, 82.205], // = (   21 x 29   ) mm  = (  0.83 x 1.14  ) in
		'G12' => [39.685, 59.528], // = (   14 x 21   ) mm  = (  0.55 x 0.83  ) in
		// ISO Press
		'RA0' => [2437.795, 3458.268], // = (  860 x 1220 ) mm  = ( 33.86 x 48.03 ) in
		'RA1' => [1729.134, 2437.795], // = (  610 x 860  ) mm  = ( 24.02 x 33.86 ) in
		'RA2' => [1218.898, 1729.134], // = (  430 x 610  ) mm  = ( 16.93 x 24.02 ) in
		'RA3' => [864.567, 1218.898], // = (  305 x 430  ) mm  = ( 12.01 x 16.93 ) in
		'RA4' => [609.449, 864.567], // = (  215 x 305  ) mm  = (  8.46 x 12.01 ) in
		'SRA0' => [2551.181, 3628.346], // = (  900 x 1280 ) mm  = ( 35.43 x 50.39 ) in
		'SRA1' => [1814.173, 2551.181], // = (  640 x 900  ) mm  = ( 25.20 x 35.43 ) in
		'SRA2' => [1275.591, 1814.173], // = (  450 x 640  ) mm  = ( 17.72 x 25.20 ) in
		'SRA3' => [907.087, 1275.591], // = (  320 x 450  ) mm  = ( 12.60 x 17.72 ) in
		'SRA4' => [637.795, 907.087], // = (  225 x 320  ) mm  = (  8.86 x 12.60 ) in
		// German DIN 476
		'4A0' => [4767.874, 6740.787], // = ( 1682 x 2378 ) mm  = ( 66.22 x 93.62 ) in
		'2A0' => [3370.394, 4767.874], // = ( 1189 x 1682 ) mm  = ( 46.81 x 66.22 ) in
		// Variations on the ISO Standard
		'A2_EXTRA' => [1261.417, 1754.646], // = (  445 x 619  ) mm  = ( 17.52 x 24.37 ) in
		'A3+' => [932.598, 1369.134], // = (  329 x 483  ) mm  = ( 12.95 x 19.02 ) in
		'A3_EXTRA' => [912.756, 1261.417], // = (  322 x 445  ) mm  = ( 12.68 x 17.52 ) in
		'A3_SUPER' => [864.567, 1440.000], // = (  305 x 508  ) mm  = ( 12.01 x 20.00 ) in
		'SUPER_A3' => [864.567, 1380.472], // = (  305 x 487  ) mm  = ( 12.01 x 19.17 ) in
		'A4_EXTRA' => [666.142, 912.756], // = (  235 x 322  ) mm  = (  9.25 x 12.68 ) in
		'A4_SUPER' => [649.134, 912.756], // = (  229 x 322  ) mm  = (  9.02 x 12.68 ) in
		'SUPER_A4' => [643.465, 1009.134], // = (  227 x 356  ) mm  = (  8.94 x 14.02 ) in
		'A4_LONG' => [595.276, 986.457], // = (  210 x 348  ) mm  = (  8.27 x 13.70 ) in
		'F4' => [595.276, 935.433], // = (  210 x 330  ) mm  = (  8.27 x 12.99 ) in
		'SO_B5_EXTRA' => [572.598, 782.362], // = (  202 x 276  ) mm  = (  7.95 x 10.87 ) in
		'A5_EXTRA' => [490.394, 666.142], // = (  173 x 235  ) mm  = (  6.81 x 9.25  ) in
		// ANSI Series
		'ANSI_E' => [2448.000, 3168.000], // = (  864 x 1118 ) mm  = ( 34.00 x 44.00 ) in
		'ANSI_D' => [1584.000, 2448.000], // = (  559 x 864  ) mm  = ( 22.00 x 34.00 ) in
		'ANSI_C' => [1224.000, 1584.000], // = (  432 x 559  ) mm  = ( 17.00 x 22.00 ) in
		'ANSI_B' => [792.000, 1224.000], // = (  279 x 432  ) mm  = ( 11.00 x 17.00 ) in
		'ANSI_A' => [612.000, 792.000], // = (  216 x 279  ) mm  = (  8.50 x 11.00 ) in
		// Traditional 'Loose' North American Paper Sizes
		'USLEDGER' => [1224.000, 792.000], // = (  432 x 279  ) mm  = ( 17.00 x 11.00 ) in
		'LEDGER' => [1224.000, 792.000], // = (  432 x 279  ) mm  = ( 17.00 x 11.00 ) in
		'ORGANIZERK' => [792.000, 1224.000], // = (  279 x 432  ) mm  = ( 11.00 x 17.00 ) in
		'BIBLE' => [792.000, 1224.000], // = (  279 x 432  ) mm  = ( 11.00 x 17.00 ) in
		'USTABLOID' => [792.000, 1224.000], // = (  279 x 432  ) mm  = ( 11.00 x 17.00 ) in
		'TABLOID' => [792.000, 1224.000], // = (  279 x 432  ) mm  = ( 11.00 x 17.00 ) in
		'ORGANIZERM' => [612.000, 792.000], // = (  216 x 279  ) mm  = (  8.50 x 11.00 ) in
		'USLETTER' => [612.000, 792.000], // = (  216 x 279  ) mm  = (  8.50 x 11.00 ) in
		'LETTER' => [612.000, 792.000], // = (  216 x 279  ) mm  = (  8.50 x 11.00 ) in
		'USLEGAL' => [612.000, 1008.000], // = (  216 x 356  ) mm  = (  8.50 x 14.00 ) in
		'LEGAL' => [612.000, 1008.000], // = (  216 x 356  ) mm  = (  8.50 x 14.00 ) in
		'GOVERNMENTLETTER' => [576.000, 756.000], // = (  203 x 267  ) mm  = (  8.00 x 10.50 ) in
		'GLETTER' => [576.000, 756.000], // = (  203 x 267  ) mm  = (  8.00 x 10.50 ) in
		'JUNIORLEGAL' => [576.000, 360.000], // = (  203 x 127  ) mm  = (  8.00 x 5.00  ) in
		'JLEGAL' => [576.000, 360.000], // = (  203 x 127  ) mm  = (  8.00 x 5.00  ) in
		// Other North American Paper Sizes
		'QUADDEMY' => [2520.000, 3240.000], // = (  889 x 1143 ) mm  = ( 35.00 x 45.00 ) in
		'SUPER_B' => [936.000, 1368.000], // = (  330 x 483  ) mm  = ( 13.00 x 19.00 ) in
		'QUARTO' => [648.000, 792.000], // = (  229 x 279  ) mm  = (  9.00 x 11.00 ) in
		'GOVERNMENTLEGAL' => [612.000, 936.000], // = (  216 x 330  ) mm  = (  8.50 x 13.00 ) in
		'FOLIO' => [612.000, 936.000], // = (  216 x 330  ) mm  = (  8.50 x 13.00 ) in
		'MONARCH' => [522.000, 756.000], // = (  184 x 267  ) mm  = (  7.25 x 10.50 ) in
		'EXECUTIVE' => [522.000, 756.000], // = (  184 x 267  ) mm  = (  7.25 x 10.50 ) in
		'ORGANIZERL' => [396.000, 612.000], // = (  140 x 216  ) mm  = (  5.50 x 8.50  ) in
		'STATEMENT' => [396.000, 612.000], // = (  140 x 216  ) mm  = (  5.50 x 8.50  ) in
		'MEMO' => [396.000, 612.000], // = (  140 x 216  ) mm  = (  5.50 x 8.50  ) in
		'FOOLSCAP' => [595.440, 936.000], // = (  210 x 330  ) mm  = (  8.27 x 13.00 ) in
		'COMPACT' => [306.000, 486.000], // = (  108 x 171  ) mm  = (  4.25 x 6.75  ) in
		'ORGANIZERJ' => [198.000, 360.000], // = (   70 x 127  ) mm  = (  2.75 x 5.00  ) in
		// Canadian standard CAN 2-9.60M
		'P1' => [1587.402, 2437.795], // = (  560 x 860  ) mm  = ( 22.05 x 33.86 ) in
		'P2' => [1218.898, 1587.402], // = (  430 x 560  ) mm  = ( 16.93 x 22.05 ) in
		'P3' => [793.701, 1218.898], // = (  280 x 430  ) mm  = ( 11.02 x 16.93 ) in
		'P4' => [609.449, 793.701], // = (  215 x 280  ) mm  = (  8.46 x 11.02 ) in
		'P5' => [396.850, 609.449], // = (  140 x 215  ) mm  = (  5.51 x 8.46  ) in
		'P6' => [303.307, 396.850], // = (  107 x 140  ) mm  = (  4.21 x 5.51  ) in
		// North American Architectural Sizes
		'ARCH_E' => [2592.000, 3456.000], // = (  914 x 1219 ) mm  = ( 36.00 x 48.00 ) in
		'ARCH_E1' => [2160.000, 3024.000], // = (  762 x 1067 ) mm  = ( 30.00 x 42.00 ) in
		'ARCH_D' => [1728.000, 2592.000], // = (  610 x 914  ) mm  = ( 24.00 x 36.00 ) in
		'BROADSHEET' => [1296.000, 1728.000], // = (  457 x 610  ) mm  = ( 18.00 x 24.00 ) in
		'ARCH_C' => [1296.000, 1728.000], // = (  457 x 610  ) mm  = ( 18.00 x 24.00 ) in
		'ARCH_B' => [864.000, 1296.000], // = (  305 x 457  ) mm  = ( 12.00 x 18.00 ) in
		'ARCH_A' => [648.000, 864.000], // = (  229 x 305  ) mm  = (  9.00 x 12.00 ) in
		// -- North American Envelope Sizes
		// - Announcement Envelopes
		'ANNENV_A2' => [314.640, 414.000], // = (  111 x 146  ) mm  = (  4.37 x 5.75  ) in
		'ANNENV_A6' => [342.000, 468.000], // = (  121 x 165  ) mm  = (  4.75 x 6.50  ) in
		'ANNENV_A7' => [378.000, 522.000], // = (  133 x 184  ) mm  = (  5.25 x 7.25  ) in
		'ANNENV_A8' => [396.000, 584.640], // = (  140 x 206  ) mm  = (  5.50 x 8.12  ) in
		'ANNENV_A10' => [450.000, 692.640], // = (  159 x 244  ) mm  = (  6.25 x 9.62  ) in
		'ANNENV_SLIM' => [278.640, 638.640], // = (   98 x 225  ) mm  = (  3.87 x 8.87  ) in
		// - Commercial Envelopes
		'COMMENV_N6_1/4' => [252.000, 432.000], // = (   89 x 152  ) mm  = (  3.50 x 6.00  ) in
		'COMMENV_N6_3/4' => [260.640, 468.000], // = (   92 x 165  ) mm  = (  3.62 x 6.50  ) in
		'COMMENV_N8' => [278.640, 540.000], // = (   98 x 191  ) mm  = (  3.87 x 7.50  ) in
		'COMMENV_N9' => [278.640, 638.640], // = (   98 x 225  ) mm  = (  3.87 x 8.87  ) in
		'COMMENV_N10' => [296.640, 684.000], // = (  105 x 241  ) mm  = (  4.12 x 9.50  ) in
		'COMMENV_N11' => [324.000, 746.640], // = (  114 x 263  ) mm  = (  4.50 x 10.37 ) in
		'COMMENV_N12' => [342.000, 792.000], // = (  121 x 279  ) mm  = (  4.75 x 11.00 ) in
		'COMMENV_N14' => [360.000, 828.000], // = (  127 x 292  ) mm  = (  5.00 x 11.50 ) in
		// - Catalogue Envelopes
		'CATENV_N1' => [432.000, 648.000], // = (  152 x 229  ) mm  = (  6.00 x 9.00  ) in
		'CATENV_N1_3/4' => [468.000, 684.000], // = (  165 x 241  ) mm  = (  6.50 x 9.50  ) in
		'CATENV_N2' => [468.000, 720.000], // = (  165 x 254  ) mm  = (  6.50 x 10.00 ) in
		'CATENV_N3' => [504.000, 720.000], // = (  178 x 254  ) mm  = (  7.00 x 10.00 ) in
		'CATENV_N6' => [540.000, 756.000], // = (  191 x 267  ) mm  = (  7.50 x 10.50 ) in
		'CATENV_N7' => [576.000, 792.000], // = (  203 x 279  ) mm  = (  8.00 x 11.00 ) in
		'CATENV_N8' => [594.000, 810.000], // = (  210 x 286  ) mm  = (  8.25 x 11.25 ) in
		'CATENV_N9_1/2' => [612.000, 756.000], // = (  216 x 267  ) mm  = (  8.50 x 10.50 ) in
		'CATENV_N9_3/4' => [630.000, 810.000], // = (  222 x 286  ) mm  = (  8.75 x 11.25 ) in
		'CATENV_N10_1/2' => [648.000, 864.000], // = (  229 x 305  ) mm  = (  9.00 x 12.00 ) in
		'CATENV_N12_1/2' => [684.000, 900.000], // = (  241 x 318  ) mm  = (  9.50 x 12.50 ) in
		'CATENV_N13_1/2' => [720.000, 936.000], // = (  254 x 330  ) mm  = ( 10.00 x 13.00 ) in
		'CATENV_N14_1/4' => [810.000, 882.000], // = (  286 x 311  ) mm  = ( 11.25 x 12.25 ) in
		'CATENV_N14_1/2' => [828.000, 1044.000], // = (  292 x 368  ) mm  = ( 11.50 x 14.50 ) in
		// Japanese (JIS P 0138-61) Standard B-Series
		'JIS_B0' => [2919.685, 4127.244], // = ( 1030 x 1456 ) mm  = ( 40.55 x 57.32 ) in
		'JIS_B1' => [2063.622, 2919.685], // = (  728 x 1030 ) mm  = ( 28.66 x 40.55 ) in
		'JIS_B2' => [1459.843, 2063.622], // = (  515 x 728  ) mm  = ( 20.28 x 28.66 ) in
		'JIS_B3' => [1031.811, 1459.843], // = (  364 x 515  ) mm  = ( 14.33 x 20.28 ) in
		'JIS_B4' => [728.504, 1031.811], // = (  257 x 364  ) mm  = ( 10.12 x 14.33 ) in
		'JIS_B5' => [515.906, 728.504], // = (  182 x 257  ) mm  = (  7.17 x 10.12 ) in
		'JIS_B6' => [362.835, 515.906], // = (  128 x 182  ) mm  = (  5.04 x 7.17  ) in
		'JIS_B7' => [257.953, 362.835], // = (   91 x 128  ) mm  = (  3.58 x 5.04  ) in
		'JIS_B8' => [181.417, 257.953], // = (   64 x 91   ) mm  = (  2.52 x 3.58  ) in
		'JIS_B9' => [127.559, 181.417], // = (   45 x 64   ) mm  = (  1.77 x 2.52  ) in
		'JIS_B10' => [90.709, 127.559], // = (   32 x 45   ) mm  = (  1.26 x 1.77  ) in
		'JIS_B11' => [62.362, 90.709], // = (   22 x 32   ) mm  = (  0.87 x 1.26  ) in
		'JIS_B12' => [45.354, 62.362], // = (   16 x 22   ) mm  = (  0.63 x 0.87  ) in
		// PA Series
		'PA0' => [2381.102, 3174.803], // = (  840 x 1120 ) mm  = ( 33.07 x 44.09 ) in
		'PA1' => [1587.402, 2381.102], // = (  560 x 840  ) mm  = ( 22.05 x 33.07 ) in
		'PA2' => [1190.551, 1587.402], // = (  420 x 560  ) mm  = ( 16.54 x 22.05 ) in
		'PA3' => [793.701, 1190.551], // = (  280 x 420  ) mm  = ( 11.02 x 16.54 ) in
		'PA4' => [595.276, 793.701], // = (  210 x 280  ) mm  = (  8.27 x 11.02 ) in
		'PA5' => [396.850, 595.276], // = (  140 x 210  ) mm  = (  5.51 x 8.27  ) in
		'PA6' => [297.638, 396.850], // = (  105 x 140  ) mm  = (  4.13 x 5.51  ) in
		'PA7' => [198.425, 297.638], // = (   70 x 105  ) mm  = (  2.76 x 4.13  ) in
		'PA8' => [147.402, 198.425], // = (   52 x 70   ) mm  = (  2.05 x 2.76  ) in
		'PA9' => [99.213, 147.402], // = (   35 x 52   ) mm  = (  1.38 x 2.05  ) in
		'PA10' => [73.701, 99.213], // = (   26 x 35   ) mm  = (  1.02 x 1.38  ) in
		// Standard Photographic Print Sizes
		'PASSPORT_PHOTO' => [99.213, 127.559], // = (   35 x 45   ) mm  = (  1.38 x 1.77  ) in
		'E' => [233.858, 340.157], // = (   82 x 120  ) mm  = (  3.25 x 4.72  ) in
		'L' => [252.283, 360.000], // = (   89 x 127  ) mm  = (  3.50 x 5.00  ) in
		'3R' => [252.283, 360.000], // = (   89 x 127  ) mm  = (  3.50 x 5.00  ) in
		'KG' => [289.134, 430.866], // = (  102 x 152  ) mm  = (  4.02 x 5.98  ) in
		'4R' => [289.134, 430.866], // = (  102 x 152  ) mm  = (  4.02 x 5.98  ) in
		'4D' => [340.157, 430.866], // = (  120 x 152  ) mm  = (  4.72 x 5.98  ) in
		'2L' => [360.000, 504.567], // = (  127 x 178  ) mm  = (  5.00 x 7.01  ) in
		'5R' => [360.000, 504.567], // = (  127 x 178  ) mm  = (  5.00 x 7.01  ) in
		'8P' => [430.866, 575.433], // = (  152 x 203  ) mm  = (  5.98 x 7.99  ) in
		'6R' => [430.866, 575.433], // = (  152 x 203  ) mm  = (  5.98 x 7.99  ) in
		'6P' => [575.433, 720.000], // = (  203 x 254  ) mm  = (  7.99 x 10.00 ) in
		'8R' => [575.433, 720.000], // = (  203 x 254  ) mm  = (  7.99 x 10.00 ) in
		'6PW' => [575.433, 864.567], // = (  203 x 305  ) mm  = (  7.99 x 12.01 ) in
		'S8R' => [575.433, 864.567], // = (  203 x 305  ) mm  = (  7.99 x 12.01 ) in
		'4P' => [720.000, 864.567], // = (  254 x 305  ) mm  = ( 10.00 x 12.01 ) in
		'10R' => [720.000, 864.567], // = (  254 x 305  ) mm  = ( 10.00 x 12.01 ) in
		'4PW' => [720.000, 1080.000], // = (  254 x 381  ) mm  = ( 10.00 x 15.00 ) in
		'S10R' => [720.000, 1080.000], // = (  254 x 381  ) mm  = ( 10.00 x 15.00 ) in
		'11R' => [790.866, 1009.134], // = (  279 x 356  ) mm  = ( 10.98 x 14.02 ) in
		'S11R' => [790.866, 1224.567], // = (  279 x 432  ) mm  = ( 10.98 x 17.01 ) in
		'12R' => [864.567, 1080.000], // = (  305 x 381  ) mm  = ( 12.01 x 15.00 ) in
		'S12R' => [864.567, 1292.598], // = (  305 x 456  ) mm  = ( 12.01 x 17.95 ) in
		// Common Newspaper Sizes
		'NEWSPAPER_BROADSHEET' => [2125.984, 1700.787], // = (  750 x 600  ) mm  = ( 29.53 x 23.62 ) in
		'NEWSPAPER_BERLINER' => [1332.283, 892.913], // = (  470 x 315  ) mm  = ( 18.50 x 12.40 ) in
		'NEWSPAPER_TABLOID' => [1218.898, 793.701], // = (  430 x 280  ) mm  = ( 16.93 x 11.02 ) in
		'NEWSPAPER_COMPACT' => [1218.898, 793.701], // = (  430 x 280  ) mm  = ( 16.93 x 11.02 ) in
		// Business Cards
		'CREDIT_CARD' => [153.014, 242.646], // = (   54 x 86   ) mm  = (  2.13 x 3.37  ) in
		'BUSINESS_CARD' => [153.014, 242.646], // = (   54 x 86   ) mm  = (  2.13 x 3.37  ) in
		'BUSINESS_CARD_ISO7810' => [153.014, 242.646], // = (   54 x 86   ) mm  = (  2.13 x 3.37  ) in
		'BUSINESS_CARD_ISO216' => [147.402, 209.764], // = (   52 x 74   ) mm  = (  2.05 x 2.91  ) in
		'BUSINESS_CARD_IT' => [155.906, 240.945], // = (   55 x 85   ) mm  = (  2.17 x 3.35  ) in
		'BUSINESS_CARD_UK' => [155.906, 240.945], // = (   55 x 85   ) mm  = (  2.17 x 3.35  ) in
		'BUSINESS_CARD_FR' => [155.906, 240.945], // = (   55 x 85   ) mm  = (  2.17 x 3.35  ) in
		'BUSINESS_CARD_DE' => [155.906, 240.945], // = (   55 x 85   ) mm  = (  2.17 x 3.35  ) in
		'BUSINESS_CARD_ES' => [155.906, 240.945], // = (   55 x 85   ) mm  = (  2.17 x 3.35  ) in
		'BUSINESS_CARD_CA' => [144.567, 252.283], // = (   51 x 89   ) mm  = (  2.01 x 3.50  ) in
		'BUSINESS_CARD_US' => [144.567, 252.283], // = (   51 x 89   ) mm  = (  2.01 x 3.50  ) in
		'BUSINESS_CARD_JP' => [155.906, 257.953], // = (   55 x 91   ) mm  = (  2.17 x 3.58  ) in
		'BUSINESS_CARD_HK' => [153.071, 255.118], // = (   54 x 90   ) mm  = (  2.13 x 3.54  ) in
		'BUSINESS_CARD_AU' => [155.906, 255.118], // = (   55 x 90   ) mm  = (  2.17 x 3.54  ) in
		'BUSINESS_CARD_DK' => [155.906, 255.118], // = (   55 x 90   ) mm  = (  2.17 x 3.54  ) in
		'BUSINESS_CARD_SE' => [155.906, 255.118], // = (   55 x 90   ) mm  = (  2.17 x 3.54  ) in
		'BUSINESS_CARD_RU' => [141.732, 255.118], // = (   50 x 90   ) mm  = (  1.97 x 3.54  ) in
		'BUSINESS_CARD_CZ' => [141.732, 255.118], // = (   50 x 90   ) mm  = (  1.97 x 3.54  ) in
		'BUSINESS_CARD_FI' => [141.732, 255.118], // = (   50 x 90   ) mm  = (  1.97 x 3.54  ) in
		'BUSINESS_CARD_HU' => [141.732, 255.118], // = (   50 x 90   ) mm  = (  1.97 x 3.54  ) in
		'BUSINESS_CARD_IL' => [141.732, 255.118], // = (   50 x 90   ) mm  = (  1.97 x 3.54  ) in
		// Billboards
		'4SHEET' => [2880.000, 4320.000], // = ( 1016 x 1524 ) mm  = ( 40.00 x 60.00 ) in
		'6SHEET' => [3401.575, 5102.362], // = ( 1200 x 1800 ) mm  = ( 47.24 x 70.87 ) in
		'12SHEET' => [8640.000, 4320.000], // = ( 3048 x 1524 ) mm  = (120.00 x 60.00 ) in
		'16SHEET' => [5760.000, 8640.000], // = ( 2032 x 3048 ) mm  = ( 80.00 x 120.00) in
		'32SHEET' => [11520.000, 8640.000], // = ( 4064 x 3048 ) mm  = (160.00 x 120.00) in
		'48SHEET' => [17280.000, 8640.000], // = ( 6096 x 3048 ) mm  = (240.00 x 120.00) in
		'64SHEET' => [23040.000, 8640.000], // = ( 8128 x 3048 ) mm  = (320.00 x 120.00) in
		'96SHEET' => [34560.000, 8640.000], // = (12192 x 3048 ) mm  = (480.00 x 120.00) in
		// -- Old European Sizes
		// - Old Imperial English Sizes
		'EN_EMPEROR' => [3456.000, 5184.000], // = ( 1219 x 1829 ) mm  = ( 48.00 x 72.00 ) in
		'EN_ANTIQUARIAN' => [2232.000, 3816.000], // = (  787 x 1346 ) mm  = ( 31.00 x 53.00 ) in
		'EN_GRAND_EAGLE' => [2070.000, 3024.000], // = (  730 x 1067 ) mm  = ( 28.75 x 42.00 ) in
		'EN_DOUBLE_ELEPHANT' => [1926.000, 2880.000], // = (  679 x 1016 ) mm  = ( 26.75 x 40.00 ) in
		'EN_ATLAS' => [1872.000, 2448.000], // = (  660 x 864  ) mm  = ( 26.00 x 34.00 ) in
		'EN_COLOMBIER' => [1692.000, 2484.000], // = (  597 x 876  ) mm  = ( 23.50 x 34.50 ) in
		'EN_ELEPHANT' => [1656.000, 2016.000], // = (  584 x 711  ) mm  = ( 23.00 x 28.00 ) in
		'EN_DOUBLE_DEMY' => [1620.000, 2556.000], // = (  572 x 902  ) mm  = ( 22.50 x 35.50 ) in
		'EN_IMPERIAL' => [1584.000, 2160.000], // = (  559 x 762  ) mm  = ( 22.00 x 30.00 ) in
		'EN_PRINCESS' => [1548.000, 2016.000], // = (  546 x 711  ) mm  = ( 21.50 x 28.00 ) in
		'EN_CARTRIDGE' => [1512.000, 1872.000], // = (  533 x 660  ) mm  = ( 21.00 x 26.00 ) in
		'EN_DOUBLE_LARGE_POST' => [1512.000, 2376.000], // = (  533 x 838  ) mm  = ( 21.00 x 33.00 ) in
		'EN_ROYAL' => [1440.000, 1800.000], // = (  508 x 635  ) mm  = ( 20.00 x 25.00 ) in
		'EN_SHEET' => [1404.000, 1692.000], // = (  495 x 597  ) mm  = ( 19.50 x 23.50 ) in
		'EN_HALF_POST' => [1404.000, 1692.000], // = (  495 x 597  ) mm  = ( 19.50 x 23.50 ) in
		'EN_SUPER_ROYAL' => [1368.000, 1944.000], // = (  483 x 686  ) mm  = ( 19.00 x 27.00 ) in
		'EN_DOUBLE_POST' => [1368.000, 2196.000], // = (  483 x 775  ) mm  = ( 19.00 x 30.50 ) in
		'EN_MEDIUM' => [1260.000, 1656.000], // = (  445 x 584  ) mm  = ( 17.50 x 23.00 ) in
		'EN_DEMY' => [1260.000, 1620.000], // = (  445 x 572  ) mm  = ( 17.50 x 22.50 ) in
		'EN_LARGE_POST' => [1188.000, 1512.000], // = (  419 x 533  ) mm  = ( 16.50 x 21.00 ) in
		'EN_COPY_DRAUGHT' => [1152.000, 1440.000], // = (  406 x 508  ) mm  = ( 16.00 x 20.00 ) in
		'EN_POST' => [1116.000, 1386.000], // = (  394 x 489  ) mm  = ( 15.50 x 19.25 ) in
		'EN_CROWN' => [1080.000, 1440.000], // = (  381 x 508  ) mm  = ( 15.00 x 20.00 ) in
		'EN_PINCHED_POST' => [1062.000, 1332.000], // = (  375 x 470  ) mm  = ( 14.75 x 18.50 ) in
		'EN_BRIEF' => [972.000, 1152.000], // = (  343 x 406  ) mm  = ( 13.50 x 16.00 ) in
		'EN_FOOLSCAP' => [972.000, 1224.000], // = (  343 x 432  ) mm  = ( 13.50 x 17.00 ) in
		'EN_SMALL_FOOLSCAP' => [954.000, 1188.000], // = (  337 x 419  ) mm  = ( 13.25 x 16.50 ) in
		'EN_POTT' => [900.000, 1080.000], // = (  318 x 381  ) mm  = ( 12.50 x 15.00 ) in
		// - Old Imperial Belgian Sizes
		'BE_GRAND_AIGLE' => [1984.252, 2948.031], // = (  700 x 1040 ) mm  = ( 27.56 x 40.94 ) in
		'BE_COLOMBIER' => [1757.480, 2409.449], // = (  620 x 850  ) mm  = ( 24.41 x 33.46 ) in
		'BE_DOUBLE_CARRE' => [1757.480, 2607.874], // = (  620 x 920  ) mm  = ( 24.41 x 36.22 ) in
		'BE_ELEPHANT' => [1746.142, 2182.677], // = (  616 x 770  ) mm  = ( 24.25 x 30.31 ) in
		'BE_PETIT_AIGLE' => [1700.787, 2381.102], // = (  600 x 840  ) mm  = ( 23.62 x 33.07 ) in
		'BE_GRAND_JESUS' => [1559.055, 2069.291], // = (  550 x 730  ) mm  = ( 21.65 x 28.74 ) in
		'BE_JESUS' => [1530.709, 2069.291], // = (  540 x 730  ) mm  = ( 21.26 x 28.74 ) in
		'BE_RAISIN' => [1417.323, 1842.520], // = (  500 x 650  ) mm  = ( 19.69 x 25.59 ) in
		'BE_GRAND_MEDIAN' => [1303.937, 1714.961], // = (  460 x 605  ) mm  = ( 18.11 x 23.82 ) in
		'BE_DOUBLE_POSTE' => [1233.071, 1601.575], // = (  435 x 565  ) mm  = ( 17.13 x 22.24 ) in
		'BE_COQUILLE' => [1218.898, 1587.402], // = (  430 x 560  ) mm  = ( 16.93 x 22.05 ) in
		'BE_PETIT_MEDIAN' => [1176.378, 1502.362], // = (  415 x 530  ) mm  = ( 16.34 x 20.87 ) in
		'BE_RUCHE' => [1020.472, 1303.937], // = (  360 x 460  ) mm  = ( 14.17 x 18.11 ) in
		'BE_PROPATRIA' => [977.953, 1218.898], // = (  345 x 430  ) mm  = ( 13.58 x 16.93 ) in
		'BE_LYS' => [898.583, 1125.354], // = (  317 x 397  ) mm  = ( 12.48 x 15.63 ) in
		'BE_POT' => [870.236, 1088.504], // = (  307 x 384  ) mm  = ( 12.09 x 15.12 ) in
		'BE_ROSETTE' => [765.354, 983.622], // = (  270 x 347  ) mm  = ( 10.63 x 13.66 ) in
		// - Old Imperial French Sizes
		'FR_UNIVERS' => [2834.646, 3685.039], // = ( 1000 x 1300 ) mm  = ( 39.37 x 51.18 ) in
		'FR_DOUBLE_COLOMBIER' => [2551.181, 3571.654], // = (  900 x 1260 ) mm  = ( 35.43 x 49.61 ) in
		'FR_GRANDE_MONDE' => [2551.181, 3571.654], // = (  900 x 1260 ) mm  = ( 35.43 x 49.61 ) in
		'FR_DOUBLE_SOLEIL' => [2267.717, 3401.575], // = (  800 x 1200 ) mm  = ( 31.50 x 47.24 ) in
		'FR_DOUBLE_JESUS' => [2154.331, 3174.803], // = (  760 x 1120 ) mm  = ( 29.92 x 44.09 ) in
		'FR_GRAND_AIGLE' => [2125.984, 3004.724], // = (  750 x 1060 ) mm  = ( 29.53 x 41.73 ) in
		'FR_PETIT_AIGLE' => [1984.252, 2664.567], // = (  700 x 940  ) mm  = ( 27.56 x 37.01 ) in
		'FR_DOUBLE_RAISIN' => [1842.520, 2834.646], // = (  650 x 1000 ) mm  = ( 25.59 x 39.37 ) in
		'FR_JOURNAL' => [1842.520, 2664.567], // = (  650 x 940  ) mm  = ( 25.59 x 37.01 ) in
		'FR_COLOMBIER_AFFICHE' => [1785.827, 2551.181], // = (  630 x 900  ) mm  = ( 24.80 x 35.43 ) in
		'FR_DOUBLE_CAVALIER' => [1757.480, 2607.874], // = (  620 x 920  ) mm  = ( 24.41 x 36.22 ) in
		'FR_CLOCHE' => [1700.787, 2267.717], // = (  600 x 800  ) mm  = ( 23.62 x 31.50 ) in
		'FR_SOLEIL' => [1700.787, 2267.717], // = (  600 x 800  ) mm  = ( 23.62 x 31.50 ) in
		'FR_DOUBLE_CARRE' => [1587.402, 2551.181], // = (  560 x 900  ) mm  = ( 22.05 x 35.43 ) in
		'FR_DOUBLE_COQUILLE' => [1587.402, 2494.488], // = (  560 x 880  ) mm  = ( 22.05 x 34.65 ) in
		'FR_JESUS' => [1587.402, 2154.331], // = (  560 x 760  ) mm  = ( 22.05 x 29.92 ) in
		'FR_RAISIN' => [1417.323, 1842.520], // = (  500 x 650  ) mm  = ( 19.69 x 25.59 ) in
		'FR_CAVALIER' => [1303.937, 1757.480], // = (  460 x 620  ) mm  = ( 18.11 x 24.41 ) in
		'FR_DOUBLE_COURONNE' => [1303.937, 2040.945], // = (  460 x 720  ) mm  = ( 18.11 x 28.35 ) in
		'FR_CARRE' => [1275.591, 1587.402], // = (  450 x 560  ) mm  = ( 17.72 x 22.05 ) in
		'FR_COQUILLE' => [1247.244, 1587.402], // = (  440 x 560  ) mm  = ( 17.32 x 22.05 ) in
		'FR_DOUBLE_TELLIERE' => [1247.244, 1927.559], // = (  440 x 680  ) mm  = ( 17.32 x 26.77 ) in
		'FR_DOUBLE_CLOCHE' => [1133.858, 1700.787], // = (  400 x 600  ) mm  = ( 15.75 x 23.62 ) in
		'FR_DOUBLE_POT' => [1133.858, 1757.480], // = (  400 x 620  ) mm  = ( 15.75 x 24.41 ) in
		'FR_ECU' => [1133.858, 1474.016], // = (  400 x 520  ) mm  = ( 15.75 x 20.47 ) in
		'FR_COURONNE' => [1020.472, 1303.937], // = (  360 x 460  ) mm  = ( 14.17 x 18.11 ) in
		'FR_TELLIERE' => [963.780, 1247.244], // = (  340 x 440  ) mm  = ( 13.39 x 17.32 ) in
		'FR_POT' => [878.740, 1133.858], // = (  310 x 400  ) mm  = ( 12.20 x 15.75 ) in
	];

	/**
	 * Initialisation.
	 *
	 * @return $this
	 */
	public function init()
	{
		parent::init();
		$this->contentStream = (new \YetiForcePDF\Objects\Basic\StreamObject())
			->setDocument($this->document)
			->init();
		if (!$this->margins) {
			$this->margins = $this->document->getDefaultMargins();
		}
		if (!$this->format) {
			$this->setFormat($this->document->getDefaultFormat());
		}
		if (!$this->orientation) {
			$this->setOrientation($this->document->getDefaultOrientation());
		}
		$this->document->getPagesObject()->addChild($this);
		$this->synchronizeFonts();
		return $this;
	}

	/**
	 * Set page group.
	 *
	 * @param int $group
	 *
	 * @return $this
	 */
	public function setGroup(int $group)
	{
		$this->group = $group;
		return $this;
	}

	/**
	 * Get page group.
	 *
	 * @return int
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Set page format.
	 *
	 * @param string $format
	 *
	 * @return $this
	 */
	public function setFormat(string $format)
	{
		$this->format = $format;
		$dimensions = self::$pageFormats[$this->format];
		if ('L' === $this->orientation) {
			$dimensions = array_reverse($dimensions);
		}
		$this->dimensions = (new Dimensions())
			->setDocument($this->document)
			->init();
		$this->dimensions
			->setWidth(Math::sub((string) $dimensions[0], Math::add((string) $this->margins['left'], (string) $this->margins['right'])))
			->setHeight(Math::sub((string) $dimensions[1], Math::add((string) $this->margins['top'], (string) $this->margins['bottom'])));
		$this->outerDimensions = (new Dimensions())
			->setDocument($this->document)
			->init();
		$this->outerDimensions->setWidth((string) $dimensions[0])->setHeight((string) $dimensions[1]);
		$this->coordinates = (new Coordinates())
			->setDocument($this->document)
			->init();
		$this->coordinates->setX((string) $this->margins['left'])->setY((string) $this->margins['top'])->init();
		return $this;
	}

	/**
	 * Get format.
	 *
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * Set page orientation.
	 *
	 * @param string $orientation
	 *
	 * @return \YetiForcePDF\Page
	 */
	public function setOrientation(string $orientation): self
	{
		$this->orientation = $orientation;
		return $this;
	}

	/**
	 * Get orientation.
	 *
	 * @return string
	 */
	public function getOrientation()
	{
		return $this->orientation;
	}

	/**
	 * Set page number.
	 *
	 * @param int $number
	 *
	 * @return $this
	 */
	public function setPageNumber(int $number)
	{
		$this->pageNumber = $number;
		return $this;
	}

	/**
	 * Get page number.
	 *
	 * @return int
	 */
	public function getPageNumber()
	{
		return $this->pageNumber;
	}

	/**
	 * Set page count.
	 *
	 * @param int $pageCount
	 *
	 * @return $this
	 */
	public function setPageCount(int $pageCount)
	{
		$this->pageCount = $pageCount;
		return $this;
	}

	/**
	 * Get page count.
	 *
	 * @return mixed
	 */
	public function getPageCount()
	{
		return $this->pageCount;
	}

	/**
	 * Get page margins.
	 *
	 * @return array
	 */
	public function getMargins(): array
	{
		return $this->margins;
	}

	/**
	 * Set page margins.
	 *
	 * @param float $left
	 * @param float $top
	 * @param float $right
	 * @param float $bottom
	 *
	 * @return $this
	 */
	public function setMargins(float $left, float $top, float $right, float $bottom)
	{
		$this->margins = [
			'left' => $left,
			'top' => $top,
			'right' => $right,
			'bottom' => $bottom,
			'horizontal' => $left + $right,
			'vertical' => $top + $bottom,
		];
		$this->setFormat($this->format);
		return $this;
	}

	/**
	 * Get page coordinates - content area basing on margins.
	 *
	 * @return \YetiForcePDF\Layout\Coordinates\Coordinates
	 */
	public function getCoordinates()
	{
		return $this->coordinates;
	}

	/**
	 * Set main box for the page.
	 *
	 * @param Box $box
	 *
	 * @return $this
	 */
	public function setBox(Box $box)
	{
		$this->box = $box;
		return $this;
	}

	/**
	 * Get main box for the page.
	 *
	 * @return Box
	 */
	public function getBox()
	{
		return $this->box;
	}

	/**
	 * Set user unit (scale of the DPI $userUnit * 72).
	 *
	 * @param float $userUnit
	 *
	 * @return \YetiForcePDF\Page
	 */
	public function setUserUnit(float $userUnit): self
	{
		$this->userUnit = $userUnit;
		return $this;
	}

	/**
	 * Add page resource.
	 *
	 * @param string                          $groupName
	 * @param string                          $resourceName
	 * @param \YetiForcePDF\Objects\PdfObject $resource
	 *
	 * @return \YetiForcePDF\Page
	 */
	public function addResource(string $groupName, string $resourceName, Objects\PdfObject $resource): self
	{
		if (!isset($this->resources[$groupName])) {
			$this->resources[$groupName] = [];
		}
		$this->resources[$groupName][$resourceName] = $resource;
		if (!$resourceName) {
			$this->doNotGroup[] = $groupName;
		}
		return $this;
	}

	/**
	 * Get resource.
	 *
	 * @param string $groupName
	 * @param string $resourceName
	 *
	 * @return \YetiForcePDF\Objects\PdfObject|null
	 */
	public function getResource(string $groupName, string $resourceName)
	{
		if (!empty($this->resources[$groupName][$resourceName])) {
			return $this->resources[$groupName][$resourceName];
		}
		return null;
	}

	/**
	 * Synchronize fonts with document fonts.
	 */
	public function synchronizeFonts()
	{
		// add all existing fonts
		foreach ($this->document->getAllFontInstances() as $fontInstance) {
			$fontNumber = $fontInstance->getNumber();
			if (!$this->getResource('Font', $fontNumber)) {
				$this->addResource('Font', $fontNumber, $fontInstance->getType0Font());
			}
		}
	}

	/**
	 * Get page content stream.
	 *
	 * @return \YetiForcePDF\Objects\Basic\StreamObject
	 */
	public function getContentStream(): StreamObject
	{
		return $this->contentStream;
	}

	/**
	 * Get page dimensions.
	 *
	 * @return \YetiForcePDF\Layout\Dimensions\Dimensions
	 */
	public function getDimensions(): Dimensions
	{
		return $this->dimensions;
	}

	/**
	 * Get page dimensions.
	 *
	 * @return \YetiForcePDF\Layout\Dimensions\Dimensions
	 */
	public function getOuterDimensions(): Dimensions
	{
		return $this->outerDimensions;
	}

	/**
	 * Layout header.
	 *
	 * @param HeaderBox $header
	 *
	 * @return $this
	 */
	protected function layoutHeader(HeaderBox $header)
	{
		$header = $header->cloneWithChildren();
		$box = $this->getBox();
		if (!$box->hasChildren()) {
			return $this;
		}
		$box->insertBefore($header, $box->getFirstChild());
		$outerWidth = $this->getOuterDimensions()->getWidth();
		$outerHeight = $this->getOuterDimensions()->getHeight();
		$header->getDimensions()->resetWidth()->resetHeight();
		$this->getDimensions()->setWidth($outerWidth);
		$this->getBox()->getDimensions()->setWidth($outerWidth);
		$this->getDimensions()->setHeight($outerHeight);
		$this->getBox()->getDimensions()->setWidth($outerHeight);
		$header->getDimensions()->setWidth($outerWidth);
		$header->setDisplayable(true);
		$header->layout();
		return $this;
	}

	/**
	 * Layout footer.
	 *
	 * @param FooterBox $footer
	 *
	 * @return $this
	 */
	protected function layoutFooter(FooterBox $footer)
	{
		$footer = $footer->cloneWithChildren();
		$box = $this->getBox();
		if (!$box->hasChildren()) {
			return $this;
		}
		$box->insertBefore($footer, $box->getFirstChild());
		$outerWidth = $this->getOuterDimensions()->getWidth();
		$outerHeight = $this->getOuterDimensions()->getHeight();
		$footer->getDimensions()->resetWidth()->resetHeight();
		$this->getDimensions()->setWidth($outerWidth);
		$this->getBox()->getDimensions()->setWidth($outerWidth);
		$this->getDimensions()->setHeight($outerHeight);
		$this->getBox()->getDimensions()->setWidth($outerHeight);
		$footer->getDimensions()->setWidth($outerWidth);
		$footer->setDisplayable(true);
		$footer->layout();
		return $this;
	}

	/**
	 * Layout watermark.
	 *
	 * @param WatermarkBox $watermark
	 *
	 * @return $this
	 */
	protected function layoutWatermark(WatermarkBox $watermark)
	{
		$watermark = $watermark->cloneWithChildren();
		$box = $this->getBox();
		if (!$box->hasChildren()) {
			return $this;
		}
		$box->insertBefore($watermark, $box->getFirstChild());
		$outerWidth = $this->getOuterDimensions()->getWidth();
		$outerHeight = $this->getOuterDimensions()->getHeight();
		$watermark->getDimensions()->resetWidth()->resetHeight();
		$this->getDimensions()->setWidth($outerWidth);
		$this->getBox()->getDimensions()->setWidth($outerWidth);
		$this->getDimensions()->setHeight($outerHeight);
		$this->getBox()->getDimensions()->setWidth($outerHeight);
		$watermark->getDimensions()->setWidth($outerWidth);
		$watermark->setDisplayable(true);
		$watermark->layout();
		return $this;
	}

	/**
	 * Set up absolute positioned boxes like header,footer, watermark.
	 *
	 * @return $this
	 */
	public function setUpAbsoluteBoxes()
	{
		$this->document->setCurrentPage($this);
		$this->getBox()->getOffset()->setLeft('0');
		$this->getBox()->getOffset()->setTop('0');
		$this->getBox()->getCoordinates()->setX('0');
		$this->getBox()->getCoordinates()->setY('0');
		$this->setMargins(0, 0, 0, 0);
		$box = $this->getBox();
		$headers = $box->getBoxesByType('HeaderBox');
		if (!empty($headers)) {
			$header = $headers[0];
			$headerClone = $header->getParent()->removeChild($header)->cloneWithChildren();
			$header->clearChildren();
			$this->document->setHeader($headerClone);
			$this->layoutHeader($headerClone);
		} elseif ($this->document->getHeader()) {
			$this->layoutHeader($this->document->getHeader());
		}
		$footers = $box->getBoxesByType('FooterBox');
		if (!empty($footers)) {
			$footer = $footers[0];
			$footerClone = $footer->getParent()->removeChild($footer)->cloneWithChildren();
			$footer->clearChildren();
			$this->document->setFooter($footerClone);
			$this->layoutFooter($footerClone);
		} elseif ($this->document->getFooter()) {
			$this->layoutFooter($this->document->getFooter());
		}
		$watermarks = $box->getBoxesByType('WatermarkBox');
		if (!empty($watermarks)) {
			$watermark = $watermarks[0];
			$watermarkClone = $watermark->getParent()->removeChild($watermark)->cloneWithChildren();
			$watermark->clearChildren();
			$this->document->setWatermark($watermarkClone);
			$this->layoutWatermark($watermarkClone);
		} elseif ($this->document->getWatermark()) {
			$this->layoutWatermark($this->document->getWatermark());
		}
		return $this;
	}

	/**
	 * Get boxes that are laid out after specified y position of the current page.
	 *
	 * @param string $yPos
	 *
	 * @return Box[]
	 */
	public function getRootChildsAfterY(string $yPos)
	{
		$boxes = [];
		$box = $this->getBox();
		$endY = $box->getCoordinates()->getEndY();
		if (Math::comp($endY, $yPos) <= 0) {
			return $boxes;
		}
		foreach ($box->getChildren() as $childBox) {
			if (Math::comp($childBox->getCoordinates()->getEndY(), $yPos) > 0) {
				$boxes[] = $childBox;
			}
		}
		return $boxes;
	}

	/**
	 * Cut box above specified position.
	 *
	 * @param Box    $child
	 * @param string $yPos
	 *
	 * @return $this
	 */
	public function cutAbove(Box $child, string $yPos)
	{
		$height = Math::sub($child->getCoordinates()->getEndY(), $yPos);
		$child->getDimensions()->setHeight($height);
		$child->getStyle()
			->setRule('border-top-width', '0')
			->setRule('padding-top', '0')
			->setRule('margin-top', '0');
		$child->setCut(static::CUT_ABOVE);
		return $this;
	}

	/**
	 * Cut box below specified position.
	 *
	 * @param Box    $child
	 * @param string $yPos
	 *
	 * @return $this
	 */
	public function cutBelow(Box $child, string $yPos)
	{
		if ($child instanceof TextBox) {
			$child->setRenderable(false);
			return $this;
		}
		$height = Math::sub($yPos, $child->getCoordinates()->getY());
		$child->getDimensions()->setHeight($height);
		$child->getStyle()
			->setRule('border-bottom-width', '0')
			->setRule('margin-bottom', '0')
			->setRule('padding-bottom', '0');
		$child->setCut(static::CUT_BELOW);
		return $this;
	}

	/**
	 * Cut box.
	 *
	 * @param Box    $box
	 * @param string $yPos
	 * @param Box    $cloned
	 *
	 * @return Box
	 */
	public function cutBox(Box $box, string $yPos, Box $cloned)
	{
		foreach ($box->getChildren() as $child) {
			if (!$child->isForMeasurement() || !$child->isRenderable()) {
				continue;
			}
			$childCoords = $child->getCoordinates();
			if (Math::comp($childCoords->getEndY(), $yPos) >= 0) {
				$childBoxes = $this->cloneAndDivideChildrenAfterY($yPos, [$child]);
				foreach ($childBoxes as $childBox) {
					$cloned->appendChild($childBox);
				}
			}
			if (Math::comp($childCoords->getY(), $yPos) >= 0) {
				$child->setRenderable(false)->setForMeasurement(false);
			}
		}
		if (Math::comp($box->getCoordinates()->getY(), $yPos) < 0 && Math::comp($box->getCoordinates()->getEndY(), $yPos) > 0) {
			$this->cutBelow($box, $yPos);
			$this->cutAbove($cloned, $yPos);
		} elseif (Math::comp($box->getCoordinates()->getY(), $yPos) >= 0) {
			$box->setRenderable(false)->setForMeasurement(false);
		}
		return $cloned;
	}

	/**
	 * Group boxes by parent.
	 *
	 * @param Box[]|null $boxes
	 * @param string     $yPos
	 *
	 * @return Box[]|null cloned boxes
	 */
	public function cloneAndDivideChildrenAfterY(string $yPos, array $boxes = null)
	{
		if (null === $boxes) {
			$boxes = [];
			foreach ($this->getBox()->getChildren() as $child) {
				if (Math::comp($child->getCoordinates()->getEndY(), $yPos) >= 0) {
					$boxes[] = $child;
				}
			}
		}
		if (empty($boxes)) {
			return null;
		}
		$clonedBoxes = [];
		foreach ($boxes as $box) {
			$cloned = $box->clone();
			$cloned->clearChildren();
			$boxCoords = $box->getCoordinates();
			if ($box instanceof TableWrapperBox && Math::comp($boxCoords->getY(), $yPos) <= 0 && Math::comp($boxCoords->getEndY(), $yPos) > 0) {
				$cloned = $this->divideTable($box, $yPos, $cloned);
			} else {
				$cloned = $this->cutBox($box, $yPos, $cloned);
			}
			$clonedBoxes[] = $cloned;
		}
		return $clonedBoxes;
	}

	/**
	 * Treat table like div? - just cut.
	 *
	 * @param TableWrapperBox $tableWrapperBox
	 * @param string          $yPos
	 *
	 * @return bool
	 */
	public function treatTableLikeDiv(TableWrapperBox $tableWrapperBox, string $yPos)
	{
		$cells = $tableWrapperBox->getBoxesByType('TableCellBox');
		foreach ($cells as $cell) {
			if (Math::comp($cell->getDimensions()->getHeight(), $this->getDimensions()->getHeight()) > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Divide overflowed table.
	 *
	 * @param Box    $tableChild
	 * @param string $yPos
	 * @param Box    $cloned
	 *
	 * @return TableWrapperBox
	 */
	protected function divideTable(Box $tableChild, string $yPos, Box $cloned)
	{
		$tableWrapperBox = $tableChild->getClosestByType('TableWrapperBox');
		if ($this->treatTableLikeDiv($tableWrapperBox, $yPos)) {
			return $this->cutBox($tableWrapperBox, $yPos, $cloned);
		}
		$pageEnd = Math::add($this->getDimensions()->getHeight(), (string) $this->margins['top']);
		if (Math::comp($tableWrapperBox->getCoordinates()->getY(), $pageEnd) >= 0) {
			// if table is below page do nothing - it will be moved to the next page and then again checked
			return $tableWrapperBox;
		}
		$newTableWrapperBox = $tableWrapperBox->clone();
		$newTableWrapperBox->getStyle()->setBox($newTableWrapperBox);
		$newTableWrapperBox->clearChildren();
		$tableBox = $tableWrapperBox->getFirstChild();
		$newTableBox = $tableBox->clone();
		$newTableBox->getStyle()->setBox($newTableBox);
		$newTableBox->clearChildren();
		$newTableWrapperBox->appendChild($newTableBox);
		$clonedFooters = $tableWrapperBox->getBoxesByType('TableFooterGroupBox', 'TableWrapperBox');
		if (!empty($clonedFooters)) {
			$clonedFooter = $clonedFooters[0]->getParent()->removeChild($clonedFooters[0])->cloneWithChildren();
		}
		$headers = $tableWrapperBox->getBoxesByType('TableHeaderGroupBox', 'TableWrapperBox');
		if (!empty($headers)) {
			$newTableBox->appendChild($headers[0]->cloneWithChildren());
		}
		// clone row groups and rows
		foreach ($tableWrapperBox->getFirstChild()->getChildren() as $tableRowGroup) {
			if (Math::comp($tableRowGroup->getCoordinates()->getEndY(), $pageEnd) < 0) {
				continue;
			}
			$moveRowGroup = $tableRowGroup->clone();
			$moveRowGroup->clearChildren();
			foreach ($tableRowGroup->getChildren() as $rowIndex => $row) {
				if (!$tableRowGroup instanceof TableFooterGroupBox && !$tableRowGroup instanceof TableHeaderGroupBox) {
					$moveRow = false;
					foreach ($row->getChildren() as $column) {
						if (Math::comp($column->getCoordinates()->getEndY(), $pageEnd) >= 0) {
							$moveRow = true;
							break;
						}
					}
					if ($moveRow) {
						if ($row->getRowSpanUp() > 0) {
							$move = [];
							// copy spanned rows too
							for ($i = $row->getRowSpanUp(); $i >= 0; --$i) {
								$spannedRowIndex = $rowIndex - $i;
								$move[] = $tableRowGroup->getChildren()[$spannedRowIndex];
							}
							// copy all next rows
							$rows = $tableRowGroup->getChildren();
							for ($i = $rowIndex, $len = \count($rows); $i < $len; ++$i) {
								$nextRow = $rows[$i];
								$move[] = $nextRow;
							}
							foreach ($move as $mr) {
								$moveRowGroup->appendChild($mr->getParent()->removeChild($mr));
							}
							break;
						}
						$moveRowGroup->appendChild($row->getParent()->removeChild($row));
					}
				}
			}
			$newTableBox->appendChild($moveRowGroup);
		}
		if (isset($clonedFooter)) {
			$newTableBox->appendChild($clonedFooter);
		}
		//remove empty rows
		$tableBox->removeEmptyRows();
		// remove original table if it was moved with all the content
		$removeSource = !$tableBox->hasChildren() || !$tableBox->containContent();
		$removeSource = $removeSource || ($tableBox->getFirstChild() instanceof TableHeaderGroupBox && 1 === \count($tableBox->getChildren()));
		if ($removeSource) {
			$tableWrapperBox->setDisplayable(false)->setRenderable(false)->setForMeasurement(false);
		}
		return $newTableWrapperBox;
	}

	/**
	 * Clone current page.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return Page
	 */
	public function cloneCurrentPage()
	{
		$newPage = clone $this;
		$newPage->setId($this->document->getActualId());
		$newPage->setPageNumber($this->getPageNumber() + 1);
		$newPage->contentStream = (new \YetiForcePDF\Objects\Basic\StreamObject())
			->setDocument($this->document)
			->init();
		$newPage->document->getPagesObject()->addChild($newPage, $this);
		$this->document->addPage($this->format, $this->orientation, $newPage, $this);
		$this->document->addObject($newPage, $this);
		return $newPage;
	}

	/**
	 * Break page after specified box.
	 *
	 * @param Box $box
	 *
	 * @return $this
	 */
	public function breakAfter(Box $box)
	{
		$box = $box->getFirstRootChild();
		if ($box->getParent()->getLastChild() === $box) {
			return $this;
		}
		$contentBoxes = [];
		$break = false;
		foreach ($box->getParent()->getChildren() as $child) {
			if ($child === $box) {
				$break = true;
			}
			if ($break && $child !== $box) {
				$contentBoxes[] = $child;
			}
		}
		$haveContent = false;
		foreach ($contentBoxes as $contentBox) {
			if ($contentBox->containContent()) {
				$haveContent = true;
				break;
			}
		}
		if (!$haveContent) {
			return $this;
		}
		$newPage = $this->cloneCurrentPage();
		$newBox = $newPage->getBox()->clone();
		$newBox->clearChildren();
		$newPage->setBox($newBox);
		$break = false;
		foreach ($box->getParent()->getChildren() as $child) {
			if ($child === $box) {
				$break = true;
			}
			if ($break && $child !== $box) {
				$newBox->appendChild($child->getParent()->removeChild($child));
			}
		}
		$newBox->layout(true);
		$this->document->setCurrentPage($newPage);
		unset($contentBoxes);
		return $this;
	}

	/**
	 * Break overflow of the current page.
	 *
	 * @param int $level Is used to stop infinite loop if something goes wrong
	 *
	 * @return $this
	 */
	public function breakOverflow(int $level = 0)
	{
		$atYPos = Math::add($this->getDimensions()->getHeight(), (string) $this->margins['top']);
		$clonedBoxes = $this->cloneAndDivideChildrenAfterY($atYPos);
		if (empty($clonedBoxes)) {
			return $this;
		}
		$newPage = $this->cloneCurrentPage();
		$newBox = $newPage->getBox();
		$newBox->clearChildren();
		foreach ($clonedBoxes as $clonedBox) {
			$newBox->appendChild($clonedBox->getParent()->removeChild($clonedBox));
		}
		$this->getBox()->getStyle()->fixDomTree();
		$this->getBox()->measureHeight(true)->measureOffset(true)->alignText()->measurePosition(true);
		$newBox->layout(true);
		$newBox->getStyle()->fixDomTree();
		$this->document->setCurrentPage($newPage);
		if (Math::comp($newBox->getDimensions()->getHeight(), $this->getDimensions()->getHeight()) > 0 && $level < 1024) {
			$newPage->breakOverflow(++$level);
		}
		unset($clonedBoxes);
		return $this;
	}

	/**
	 * Layout page resources.
	 *
	 * @return string
	 */
	public function renderResources(): string
	{
		$rendered = [
			'  /Resources <<',
		];
		foreach ($this->resources as $groupName => $resourceGroup) {
			if (!\in_array($groupName, $this->doNotGroup)) {
				$rendered[] = "    /$groupName <<";
				foreach ($resourceGroup as $resourceName => $resourceObject) {
					$rendered[] = "      /$resourceName " . $resourceObject->getReference();
				}
				$rendered[] = '    >>';
			} else {
				$str = "    /$groupName ";
				foreach ($resourceGroup as $resourceName => $resourceObject) {
					$str .= $resourceObject->getReference();
				}
				$rendered[] = $str;
			}
		}
		$rendered[] = '  >>';
		return implode("\n", $rendered);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		$dimensions = $this->getOuterDimensions();
		return implode("\n", [
			$this->getRawId() . ' obj',
			'<<',
			'  /Type /Page',
			'  /Parent ' . $this->parent->getReference(),
			'  /MediaBox [0 0 ' . $dimensions->getWidth() . ' ' . $dimensions->getHeight() . ']',
			'  /BleedBox [' . $this->margins['left'] . ' ' . $this->margins['top'] . ' ' . $dimensions->getWidth() . ' ' . $dimensions->getHeight() . ']',
			'  /UserUnit ' . $this->userUnit,
			'  /Rotate 0',
			$this->renderResources(),
			'  /Contents ' . $this->contentStream->getReference(),
			'>>',
			'endobj',
		]);
	}

	public function __clone()
	{
		$this->box = clone $this->box->cloneWithChildren();
		$this->coordinates = clone $this->coordinates;
		$this->coordinates->setBox($this->box);
		$this->contentStream = clone $this->contentStream;
		$this->dimensions = clone $this->dimensions;
		$this->outerDimensions = clone $this->dimensions;
		$currentResources = $this->resources;
		$this->resources = [];
		foreach ($currentResources as $groupName => $resources) {
			foreach ($resources as $resourceName => $resource) {
				$this->resources[$groupName][$resourceName] = clone $resource;
			}
		}
	}
}
