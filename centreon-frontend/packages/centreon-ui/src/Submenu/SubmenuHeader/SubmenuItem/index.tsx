import makeStyles from '@mui/styles/makeStyles';
import { Typography, Theme } from '@mui/material';
import { CreateCSSProperties } from '@mui/styles';

import { Colors, SeverityCode } from '../../../StatusChip';
import { getStatusColors } from '../../..';

export interface StyleProps {
  severityCode: SeverityCode;
}

const useStyles = makeStyles<Theme, StyleProps>((theme) => {
  const getStatusIconColors = (severityCode: SeverityCode): Colors =>
    getStatusColors({
      severityCode,
      theme,
    });

  return {
    count: {
      color: theme.palette.common.white,
      fontSize: theme.typography.body1.fontSize,
      lineHeight: '1',
    },
    statusCounter: ({ severityCode }): CreateCSSProperties<StyleProps> => ({
      background: getStatusIconColors(severityCode)?.backgroundColor,
      borderRadius: '50%',
      height: theme.spacing(1),
      width: theme.spacing(1),
    }),
    submenuItem: {
      '&:hover': {
        background: theme.palette.grey[500],
      },
      alignItems: 'center',
      borderBottom: `1px solid ${theme.palette.grey[500]}`,
      display: 'flex',
      justifyContent: 'space-between',
      padding: theme.spacing(1, 0),
      position: 'relative',
      width: '100%',
    },
    title: {
      alignItems: 'center',
      color: theme.palette.common.white,
      display: 'flex',
    },
    titleContent: {
      lineHeight: '1',
      marginLeft: theme.spacing(1.5),
    },
  };
});

interface Props {
  countTestId?: string;
  severityCode: SeverityCode;
  submenuCount: string | number;
  submenuTitle: string;
  titleTestId?: string;
}

const SubmenuItem = ({
  severityCode,
  submenuTitle,
  submenuCount,
  titleTestId,
  countTestId,
}: Props): JSX.Element => {
  const classes = useStyles({ severityCode });

  return (
    <li className={classes.submenuItem}>
      <div className={classes.title} data-testid={titleTestId}>
        <div className={classes.statusCounter} />
        <Typography className={classes.titleContent} variant="body2">
          {submenuTitle}
        </Typography>
      </div>
      <div className={classes.count} data-testid={countTestId}>
        <Typography variant="body2">{submenuCount}</Typography>
      </div>
    </li>
  );
};

export default SubmenuItem;