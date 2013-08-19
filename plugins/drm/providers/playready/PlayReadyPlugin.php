<?php
/**
 * @package plugins.playReady
 */
class PlayReadyPlugin extends KalturaPlugin implements IKalturaEnumerator, IKalturaServices , IKalturaPermissions, IKalturaObjectLoader, IKalturaSearchDataContributor, IKalturaPending
{
	const PLUGIN_NAME = 'playReady';
	const SEARCH_DATA_SUFFIX = 's';
	
	const ENTRY_CUSTOM_DATA_PLAY_READY_KEY_ID = 'play_ready_key_id';
	
	/* (non-PHPdoc)
	 * @see IKalturaPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$drmDependency = new KalturaDependency(DrmPlugin::getPluginName());
		
		return array($drmDependency);
	}
			
	/* (non-PHPdoc)
	 * @see IKalturaEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{	
		if(is_null($baseEnumName))
			return array('PlayReadyAccessControlActionType', 'PlayReadyLicenseScenario', 'PlayReadyLicenseType', 'PlayReadyProviderType');		
		if($baseEnumName == 'accessControlActionType')
			return array('PlayReadyAccessControlActionType');
		if($baseEnumName == 'DrmLicenseScenario')
			return array('PlayReadyLicenseScenario');
		if($baseEnumName == 'DrmLicenseType')
			return array('PlayReadyLicenseType');
		if($baseEnumName == 'DrmProviderType')
			return array('PlayReadyProviderType');		
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'KalturaDrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new KalturaPlayReadyProfile();
	
		if($baseClass == 'KalturaDrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new KalturaPlayReadyPolicy();
		
		if($baseClass == 'KalturaAccessControlAction' && $enumValue == PlayReadyAccessControlActionType::DRM_POLICY)
			return new KalturaAccessControlPlayReadyPolicyAction();
			
		if($baseClass == 'DrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new PlayReadyProfile();
			
		if($baseClass == 'DrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new PlayReadyPolicy();
			
		if($baseClass == 'kAccessControlAction' && $enumValue == PlayReadyAccessControlActionType::DRM_POLICY)
			return new kAccessControlPlayReadyPolicyAction();
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{	
		if($baseClass == 'KalturaDrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'KalturaPlayReadyProfile';
	
		if($baseClass == 'KalturaDrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'KalturaPlayReadyPolicy';
		
		if($baseClass == 'KalturaAccessControlAction' && $enumValue == PlayReadyAccessControlActionType::DRM_POLICY)
			return 'KalturaAccessControlPlayReadyPolicyAction';
			
		if($baseClass == 'DrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'PlayReadyProfile';
			
		if($baseClass == 'DrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'PlayReadyPolicy';
			
		if($baseClass == 'kAccessControlAction' && $enumValue == PlayReadyAccessControlActionType::DRM_POLICY)
			return 'kAccessControlPlayReadyPolicyAction';
			
		return null;
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IKalturaEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getCoreValue($type, $valueName)
	{
		$value = self::getPluginName() . IKalturaEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return kPluginableEnumsManager::apiToCore($type, $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getPlayReadyProviderCoreValue()
	{
		$value = self::getPluginName() . IKalturaEnumerator::PLUGIN_VALUE_DELIMITER . PlayReadyProviderType::PLAY_READY;
		return kPluginableEnumsManager::apiToCore('DrmProviderType', $value);
	}

	/* (non-PHPdoc)
	 * @see IKalturaServices::getServicesMap()
	 */
	public static function getServicesMap() {
		$map = array(
			'playReadyDrm' => 'PlayReadyDrmService',
		);
		return $map;	
	}

	/* (non-PHPdoc)
	 * @see IKalturaPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId) {	
		if (in_array($partnerId, array(Partner::ADMIN_CONSOLE_PARTNER_ID, Partner::BATCH_PARTNER_ID)))
			return true;		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return false;
		return $partner->getPluginEnabled(self::PLUGIN_NAME);			
	}
	
	public static function getPlayReadyKeyIdSearchData($keyId)
	{
		return self::getPluginName() . $keyId . self::SEARCH_DATA_SUFFIX;
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if($object instanceof entry)
		{
			$keyId = $object->getFromCustomData(self::ENTRY_CUSTOM_DATA_PLAY_READY_KEY_ID);
			if($keyId)
			{
				$searchData = self::getPlayReadyKeyIdSearchData($keyId);			
				return array('plugins_data' => $searchData);
			}
		}
			
		return null;
	}
	
	public static function getPlayReadyConfigParam($key)
	{
		return DrmPlugin::getConfigParam(self::PLUGIN_NAME, $key);
	}
}
