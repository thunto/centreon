import { Formik, FormikHelpers, FormikValues } from 'formik';
import * as Yup from 'yup';

import { makeStyles } from '@mui/styles';

import FormButtons from './FormButtons';
import Inputs from './Inputs';
import { Group, InputProps } from './Inputs/models';

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 0, 0),
  },
}));

interface Props<T> {
  Buttons?: React.ComponentType;
  groups?: Array<Group>;
  initialValues: T;
  inputs: Array<InputProps>;
  isCollapsible?: boolean;
  isLoading?: boolean;
  submit: (values: T, bag: FormikHelpers<T>) => void | Promise<void>;
  validate?: (values: FormikValues) => void;
  validationSchema: Yup.SchemaOf<T>;
}

const Form = <T extends object>({
  initialValues,
  validate,
  validationSchema,
  submit,
  groups,
  inputs,
  Buttons = FormButtons,
  isLoading = false,
  isCollapsible = false,
}: Props<T>): JSX.Element => {
  const classes = useStyles();

  if (isLoading) {
    return (
      <Inputs
        isLoading
        groups={groups}
        inputs={inputs}
        isCollapsible={isCollapsible}
      />
    );
  }

  return (
    <Formik<T>
      enableReinitialize
      validateOnBlur
      validateOnMount
      initialValues={initialValues}
      validate={validate}
      validationSchema={validationSchema}
      onSubmit={submit}
    >
      <div className={classes.formContainer}>
        <Inputs groups={groups} inputs={inputs} isCollapsible={isCollapsible} />
        <Buttons />
      </div>
    </Formik>
  );
};

export default Form;
