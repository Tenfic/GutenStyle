import type { TablePresentation } from '../../table/table-style';

export function TablePreview( {
	presentation,
}: {
	presentation: TablePresentation;
} ) {
	return (
		<div className="gutenstyle-preview-frame">
			<figure
				className={ `wp-block-table ${ presentation.className }` }
				style={ presentation.wrapperStyle }
			>
				<table style={ presentation.tableStyle }>
					<thead>
						<tr>
							<th>Plan</th>
							<th>Projects</th>
							<th>Storage</th>
							<th>Support</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Starter</td>
							<td>3</td>
							<td>5 GB</td>
							<td>Community</td>
						</tr>
						<tr>
							<td>Studio</td>
							<td>15</td>
							<td>50 GB</td>
							<td>Email</td>
						</tr>
						<tr>
							<td>Agency</td>
							<td>Unlimited</td>
							<td>250 GB</td>
							<td>Priority</td>
						</tr>
					</tbody>
				</table>
				<figcaption>GutenStyle product comparison</figcaption>
			</figure>
		</div>
	);
}
