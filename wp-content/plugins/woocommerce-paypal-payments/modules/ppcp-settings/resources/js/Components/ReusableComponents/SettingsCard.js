import data from '../../utils/data';

const SettingsCard = ( props ) => {
	let className = 'ppcp-r-settings-card';

	if ( props?.className ) {
		className += ' ' + props.className;
	}
	return (
		<div className={ className }>
			<div className="ppcp-r-settings-card__header">
				<div className="ppcp-r-settings-card__content-inner">
					<span className="ppcp-r-settings-card__title">
						{ props.title }
					</span>
					<p className="ppcp-r-settings-card__description">
						{ props.description }
					</p>
				</div>
			</div>
			<div className="ppcp-r-settings-card__content">
				{ props.children }
			</div>
		</div>
	);
};

export default SettingsCard;
