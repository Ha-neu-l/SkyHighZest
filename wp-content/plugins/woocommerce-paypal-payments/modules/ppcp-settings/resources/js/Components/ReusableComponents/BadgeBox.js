import data from '../../utils/data';
import TitleBadge, { TITLE_BADGE_INFO } from './TitleBadge';
import { __ } from '@wordpress/i18n';

const BadgeBox = ( props ) => {
	const titleSize =
		props.titleType && props.titleType === BADGE_BOX_TITLE_BIG
			? BADGE_BOX_TITLE_BIG
			: BADGE_BOX_TITLE_SMALL;

	const titleTextClassName =
		'ppcp-r-badge-box__title-text ' +
		`ppcp-r-badge-box__title-text--${ titleSize }`;

	const titleBaseClassName = 'ppcp-r-badge-box__title';
	const titleClassName = props.imageBadge
		? `${ titleBaseClassName } ppcp-r-badge-box__title--has-image-badge`
		: titleBaseClassName;
	return (
		<div className="ppcp-r-badge-box">
			<span className={ titleClassName }>
				<span className={ titleTextClassName }>{ props.title }</span>

				{ props.imageBadge && (
					<span className="ppcp-r-badge-box__title-image-badge">
						{ props.imageBadge.map( ( badge ) =>
							data().getImage( badge )
						) }
					</span>
				) }

				{ props.textBadge && (
					<TitleBadge
						type={ TITLE_BADGE_INFO }
						text={ props.textBadge }
					/>
				) }
			</span>
			<div className="ppcp-r-badge-box__description">
				{ props?.description && (
					<p
						className="ppcp-r-badge-box__description"
						dangerouslySetInnerHTML={ {
							__html: props.description,
						} }
					></p>
				) }
			</div>
		</div>
	);
};

export const BADGE_BOX_TITLE_BIG = 'big';
export const BADGE_BOX_TITLE_SMALL = 'small';
export default BadgeBox;
