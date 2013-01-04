<?php
/**
 * GeoHash class definition
 *
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Defines a class to handle geolocation conversion lat/lng <-> geohash
 *
 * It can also perform a neighbouring search of a geohash, which can be
 * used to perform a proximity search.
 */
final class GeoHash {

    private $hash; /**< @type string geohash */

    private $latitude; /**< @type float latitude */
    private $longitude; /**< @type float longitude */
    private $precision; /**> @type float precision (in kms) */


    private static $_ratios = array(12 => 0.000018, 11 => 0.000075, 10 => 0.00060, 9 => 0.00478,
                                     8 => 0.019,     7 => 0.076,     6 => 0.61,    5 => 2.4,
                                     4 => 20,        3 => 78,        2 => 630,     1 => 2500);

    /**
     * Creates a new Geohash object given specified lat/lon/pre parameters
     *
     * @param float $latitude latitude
     * @param float $longitude longitude
     * @param float $precision precision for geohash, in kms (<1 for less than km precision)
     * @return GeoHash new instance
     */
    public static function create($latitude, $longitude, $precision)
    {
        $new = new self;
        $new->setLatitude($latitude);
        $new->setLongitude($longitude);
        $new->setPrecision($precision);
        return $new;
    }

    /**
     * Creates a new Geohash object given a geohash. Converts back to lat/lng/pre
     *
     * @param string $geohash the geohash
     * @return GeoHash new instance
     */
    public static function createFromHash($geohash)
    {
        $new = new self;
        $new->setHash($geohash);
        return $new;
    }

    /**
     * Static helper to get the geohash of given lat/long with given precision
     *
     * @param float $latitude latitude
     * @param float $longitude longitude
     * @param float $precision precision for geohash, in kms (<1 for less than km precision)
     * @return string the geohash
     */
    public static function hash($latitude, $longitude, $precision=null)
    {
        $table = "0123456789bcdefghjkmnpqrstuvwxyz";
        $lng = $longitude;
        $lat = $latitude;
        if(is_null($precision)) {
          $precision = self::$_ratios[10]; // Default to 10 chars for geohash
        }
        $p = self::errorFromPrecision($precision);
        $minlat =  -90;
        $maxlat =   90;
        $minlng = -180;
        $maxlng =  180;
        $latE   =   90;
        $lngE   =  180;
        $i=0;
        $hash = "";
        $error = 180;
        while(strlen($hash) < $p) {
          $chr = 0;
          for($b=4;$b>=0;--$b) {
            if((1&$b) == (1&$i)) { // even char, even bit OR odd char, odd bit...a lng
              $next = ($minlng+$maxlng)/2;
              if($lng>$next) {
                $chr |= pow(2,$b);
                $minlng = $next;
              } else {
                $maxlng = $next;
              }
              $lngE /= 2;
            } else { // odd char, even bit OR even char, odd bit...a lat
              $next = ($minlat+$maxlat)/2;
              if($lat>$next) {
                $chr |= pow(2,$b);
                $minlat = $next;
              } else {
                $maxlat = $next;
              }
              $latE /= 2;
            }
          }
          $hash .= $table[$chr];
          $i++;
          $error = min($latE,$lngE);
        }
        return $hash;
    }


    /**
     * Returns the neighbouring geohashes (3x3 grid) from set location
     *
     * @return array(GeoHash) neighbouring geohashes
     */
    public function getNeighbours()
    {
        $ary_tmp = $this->getInterval();

        list($ary_lat, $ary_lon) = $ary_tmp;
        $delta_lat = $ary_lat[1] - $ary_lat[0];
        $delta_lon = $ary_lon[1] - $ary_lon[0];
        $lat = ($ary_lat[0] + $ary_lat[1]) / 2;
        $lon = ($ary_lon[0] + $ary_lon[1]) / 2;

        $ary_geohash = array();
        foreach (range(-1,1) as $i) {
            foreach (range(-1,1) as $j) {
                //if (abs($i) == 1 && abs($j) == 1) continue;
                if($i == 0 && $j == 0) continue;

                $lat_tmp = $lat + $delta_lat * $i;
                if($lat_tmp < -90.0) $lat_tmp += 180.0;
                else if($lat_tmp > 90.0) $lat_tmp -= 180.0;
                $lon_tmp = $lon + $delta_lon * $j;
                if($lon_tmp < -180.0) $lon_tmp += 360.0;
                else if($lon_tmp > 180.0) $lon_tmp -= 360.0;

                $geo = self::create($lat_tmp, $lon_tmp, $this->getPrecision());
                $ary_geohash[] = $geo;
            }
        }
        return $ary_geohash;
    }

    public function getInterval()
    {
        $table = "0123456789bcdefghjkmnpqrstuvwxyz";
        $hash = strtolower($this->getHash());
        $minlat =  -90;
        $maxlat =   90;
        $minlng = -180;
        $maxlng =  180;
        $latE   =   90;
        $lngE   =  180;

        for($i=0,$c=strlen($hash);$i<$c;$i++) {
          $v = strpos($table,$hash[$i]);
          if(1&$i) {
            if(16&$v)$minlat = ($minlat+$maxlat)/2; else $maxlat = ($minlat+$maxlat)/2;
            if(8&$v) $minlng = ($minlng+$maxlng)/2; else $maxlng = ($minlng+$maxlng)/2;
            if(4&$v) $minlat = ($minlat+$maxlat)/2; else $maxlat = ($minlat+$maxlat)/2;
            if(2&$v) $minlng = ($minlng+$maxlng)/2; else $maxlng = ($minlng+$maxlng)/2;
            if(1&$v) $minlat = ($minlat+$maxlat)/2; else $maxlat = ($minlat+$maxlat)/2;
            $latE /= 8;
            $lngE /= 4;
          } else {
            if(16&$v)$minlng = ($minlng+$maxlng)/2; else $maxlng = ($minlng+$maxlng)/2;
            if(8&$v) $minlat = ($minlat+$maxlat)/2; else $maxlat = ($minlat+$maxlat)/2;
            if(4&$v) $minlng = ($minlng+$maxlng)/2; else $maxlng = ($minlng+$maxlng)/2;
            if(2&$v) $minlat = ($minlat+$maxlat)/2; else $maxlat = ($minlat+$maxlat)/2;
            if(1&$v) $minlng = ($minlng+$maxlng)/2; else $maxlng = ($minlng+$maxlng)/2;
            $latE /= 4;
            $lngE /= 8;
          }
        }
        return array(array($minlat, $maxlat, $latE), array($minlng, $maxlng, $lngE));
    }


    /**
     * Returns the hash
     * @return string
     */
    public function getHash() {
        if(!$this->hash) {
            if(empty($this->latitude)) throw new Exception("Latitude is required");
            if(empty($this->longitude)) throw new Exception("Longitude is required");
            $this->hash = self::hash($this->latitude, $this->longitude, $this->precision);
        }
        return $this->hash;
    }

    /**
     * Set a hash, this will clear any latitude/longitude or precision set
     * @return GeoHash
     */
    public function setHash($hash) {
        $this->hash = $hash;
        $this->parseHash();
        return $this;
    }

    /**
     * Get the latitude
     * @return float
     */
    public function getLatitude() {
        return $this->latitude;
    }

    /**
     * Set a latitude, this will clear any hash
     * @return GeoHash
     */
    public function setLatitude($latitude) {
        $this->hash = null;
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * Get the longitude
     * @return float
     */
    public function getLongitude() {
        return $this->longitude;
    }

    /**
     * Set a latitude, this will clear any hash
     * @return GeoHash
     */
    public function setLongitude($longitude) {
        $this->hash = null;
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * Gets the precision, in kms
     * @return float
     */
    public function getPrecision() {
        return $this->precision;
    }

    /**
     * Set a precision, in kms, clears any hash
     * @return GeoHash
     */
    public function setPrecision($precision) {
        $this->hash = null;
        $this->precision = $precision;
        return $this;
    }

    /**
     * Return the hash, obviously, to print out
     * @return string
     */
    public function __toString() {
        return $this->getHash();
    }



    private function clearCoords() {
        $this->latitude  = null;
        $this->longitude = null;
        $this->precision = null;
    }

    private function parseHash() {
        $latlngInterval = $this->getInterval();

        list ($minlat, $maxlat, $latE) = $latlngInterval[0];
        list ($minlng, $maxlng, $lngE) = $latlngInterval[1];
        $this->latitude  = round(($minlat+$maxlat)/2, max(1, -round(log10($latE)))-1);
        $this->longitude = round(($minlng+$maxlng)/2, max(1, -round(log10($lngE)))-1);
        $this->precision = self::precisionFromHash($this->hash);
    }


    private static function errorRatio($lat, $lng, $precision)
    {
        $EARTH_RADIUS = 6378.1;
        // Length of a degree latitude is approximately the same for all latitudes, ~1km diff.
        $approxLat1 = 111.13294;
        // Longitude varies depending on latitude : some basic trig. here
        // Get radius of disc at given latitude, divide by 360 for a degree longitude distance.
        $approxLng1 = 2*pi()*$EARTH_RADIUS*cos($lat)/360;
        // Measurement error : $precision in kms.
        // cross product to get latitude/longitude (min) value from which precision is reached
        $error = abs(min($precision/$approxLat1, $precision/$approxLng1));
        return $error;
    }

    private static function errorFromPrecision($precision)
    {
        $i = 1;
        while ($i < count(self::$_ratios)-1 && self::$_ratios[$i] >= $precision) { ++$i; }
        return max(1, $i-1);
    }

    // Accuracy in kms based on length of geohash string (limit to 12)
    private static function precisionFromHash($hash)
    {
        return self::$_ratios[min(count(self::$_ratios), strlen($hash))];
    }
}
